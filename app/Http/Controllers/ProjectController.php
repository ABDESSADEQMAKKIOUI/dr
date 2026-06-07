<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Customer;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with(['customer', 'user'])
            ->withCount('tasks')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(20);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();
        return view('projects.create', compact('customers', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,active,on_hold,completed,cancelled',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customers,id',
            'budget'      => 'nullable|numeric|min:0',
            'user_id'     => 'required|exists:users,id',
        ]);

        $project = Project::create($data);

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load(['customer', 'user', 'employees']);
        $tasks = $project->tasks()->with('assignee')->get()->groupBy('status');

        $statuses = ['todo', 'in_progress', 'in_review', 'done'];
        $users    = User::orderBy('name')->get();

        return view('projects.show', compact('project', 'tasks', 'statuses', 'users'));
    }

    public function edit(Project $project)
    {
        $customers = Customer::orderBy('name')->get();
        $users     = User::orderBy('name')->get();
        return view('projects.edit', compact('project', 'customers', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,active,on_hold,completed,cancelled',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customers,id',
            'budget'      => 'nullable|numeric|min:0',
            'user_id'     => 'required|exists:users,id',
        ]);

        $project->update($data);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->tasks()->delete();
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    // Task management within project
    public function storeTask(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $data['project_id'] = $project->id;
        Task::create($data);

        return back()->with('success', 'Task added.');
    }

    public function updateTask(Request $request, Project $project, Task $task)
    {
        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'status'      => 'sometimes|in:todo,in_progress,in_review,done',
            'priority'    => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $task->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task updated.');
    }

    public function destroyTask(Project $project, Task $task)
    {
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }
}
