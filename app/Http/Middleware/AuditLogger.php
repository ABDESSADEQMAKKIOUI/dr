<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class AuditLogger
{
    protected array $ignoredActions = ['index', 'show'];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Logger seulement les actions de modification (POST, PUT, DELETE)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->log($request, $response);
        }

        return $response;
    }

    protected function log(Request $request, $response): void
    {
        try {
            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => $this->getAction($request),
                'model_type' => $this->getModelType($request),
                'model_id' => $this->getModelId($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'data' => $this->sanitizeData($request->all()),
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer la requête si le log échoue
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }

    protected function getAction(Request $request): string
    {
        $route = $request->route();
        return $route ? $route->getActionMethod() : 'unknown';
    }

    protected function getModelType(Request $request): ?string
    {
        $route = $request->route();
        $controller = $route ? $route->getControllerClass() : null;
        
        if ($controller) {
            return class_basename($controller);
        }

        return null;
    }

    protected function getModelId(Request $request): ?int
    {
        $route = $request->route();
        return $route?->parameter('id');
    }

    protected function sanitizeData(array $data): array
    {
        // Supprimer les données sensibles
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }
}
