@extends('layouts.app')
@section('title', 'Documentation')
@php
$pageTitle = 'Documentation';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>'Documentation','url'=>'']];
@endphp
@section('content')
<div style="--accent:{{ $accent }}" x-data="{ lang: 'fr', section: 'dashboard' }">

<style>
.doc-nav a { display:block; padding:.45rem 1rem; border-radius:.5rem; font-size:.8125rem; color:#64748b; text-decoration:none; margin-bottom:2px; }
.doc-nav a:hover, .doc-nav a.active { background:color-mix(in srgb,var(--accent) 10%,#fff); color:var(--accent); font-weight:600; }
.doc-nav .nav-group { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; padding:.75rem 1rem .25rem; }
.lang-btn { padding:.35rem .9rem; border-radius:.5rem; font-size:.8rem; font-weight:600; cursor:pointer; border:1.5px solid #e2e8f0; background:#fff; color:#64748b; transition:all .15s; }
.lang-btn.active, .lang-btn:hover { background:var(--accent); color:#fff; border-color:var(--accent); }
.doc-section { display:none; }
.doc-section.active { display:block; }
.step { display:flex; gap:1rem; margin-bottom:1.25rem; align-items:flex-start; }
.step-num { flex-shrink:0; width:2rem; height:2rem; border-radius:50%; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.8125rem; font-weight:700; }
.step-body h4 { font-size:.9375rem; font-weight:600; color:#1e293b; margin-bottom:.25rem; }
.step-body p { font-size:.875rem; color:#475569; margin:0; line-height:1.6; }
.tip { background:#f0fdf4; border-left:3px solid #22c55e; padding:.75rem 1rem; border-radius:0 .5rem .5rem 0; font-size:.875rem; color:#166534; margin:1rem 0; }
.warn { background:#fffbeb; border-left:3px solid #f59e0b; padding:.75rem 1rem; border-radius:0 .5rem .5rem 0; font-size:.875rem; color:#92400e; margin:1rem 0; }
.doc-card { background:#fff; border:1px solid #e2e8f0; border-radius:.875rem; padding:1.5rem; margin-bottom:1.5rem; }
.doc-card h3 { font-size:1.0625rem; font-weight:700; color:#1e293b; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
.doc-card h3 svg { width:1.125rem; height:1.125rem; color:var(--accent); }
[dir=rtl] .step { flex-direction:row-reverse; }
[dir=rtl] .step-body { text-align:right; }
[dir=rtl] .tip, [dir=rtl] .warn { border-left:none; border-right:3px solid #22c55e; border-radius:.5rem 0 0 .5rem; }
[dir=rtl] .warn { border-right-color:#f59e0b; }
</style>

{{-- Header --}}
<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg></div>
  <div>
    <p class="pg-hero-title">Documentation</p>
    <p class="pg-hero-sub">Complete guide — English · Français · العربية</p>
  </div>
  <div class="flex gap-2 ml-auto mt-2 md:mt-0">
    <button class="lang-btn" :class="{active:lang==='en'}" @click="lang='en'">EN</button>
    <button class="lang-btn" :class="{active:lang==='fr'}" @click="lang='fr'">FR</button>
    <button class="lang-btn" :class="{active:lang==='ar'}" @click="lang='ar'">ع</button>
  </div>
</div>

<div class="flex gap-6">

  {{-- Left Nav --}}
  <div class="hidden lg:block" style="width:220px;flex-shrink:0">
    <div style="position:sticky;top:5rem;background:#fff;border:1px solid #e2e8f0;border-radius:.875rem;padding:.75rem 0" class="doc-nav">
      <div class="nav-group" x-text="lang==='ar'?'الأقسام':lang==='fr'?'Sections':'Sections'"></div>
      <a href="#" @click.prevent="section='dashboard'" :class="{active:section==='dashboard'}">
        <span x-show="lang==='en'">Dashboard</span><span x-show="lang==='fr'">Tableau de bord</span><span x-show="lang==='ar'">لوحة التحكم</span>
      </a>
      <a href="#" @click.prevent="section='products'" :class="{active:section==='products'}">
        <span x-show="lang==='en'">Products</span><span x-show="lang==='fr'">Produits</span><span x-show="lang==='ar'">المنتجات</span>
      </a>
      <a href="#" @click.prevent="section='stock'" :class="{active:section==='stock'}">
        <span x-show="lang==='en'">Stock</span><span x-show="lang==='fr'">Stock</span><span x-show="lang==='ar'">المخزون</span>
      </a>
      <a href="#" @click.prevent="section='purchases'" :class="{active:section==='purchases'}">
        <span x-show="lang==='en'">Purchases</span><span x-show="lang==='fr'">Achats</span><span x-show="lang==='ar'">المشتريات</span>
      </a>
      <a href="#" @click.prevent="section='sales'" :class="{active:section==='sales'}">
        <span x-show="lang==='en'">Sales</span><span x-show="lang==='fr'">Ventes</span><span x-show="lang==='ar'">المبيعات</span>
      </a>
      <a href="#" @click.prevent="section='invoices'" :class="{active:section==='invoices'}">
        <span x-show="lang==='en'">Invoices & Quotes</span><span x-show="lang==='fr'">Factures & Devis</span><span x-show="lang==='ar'">الفواتير والعروض</span>
      </a>
      <a href="#" @click.prevent="section='payments'" :class="{active:section==='payments'}">
        <span x-show="lang==='en'">Payments</span><span x-show="lang==='fr'">Paiements</span><span x-show="lang==='ar'">المدفوعات</span>
      </a>
      <a href="#" @click.prevent="section='expenses'" :class="{active:section==='expenses'}">
        <span x-show="lang==='en'">Expenses</span><span x-show="lang==='fr'">Dépenses</span><span x-show="lang==='ar'">المصروفات</span>
      </a>
      <a href="#" @click.prevent="section='customers'" :class="{active:section==='customers'}">
        <span x-show="lang==='en'">Customers</span><span x-show="lang==='fr'">Clients</span><span x-show="lang==='ar'">العملاء</span>
      </a>
      <a href="#" @click.prevent="section='reports'" :class="{active:section==='reports'}">
        <span x-show="lang==='en'">Reports</span><span x-show="lang==='fr'">Rapports</span><span x-show="lang==='ar'">التقارير</span>
      </a>
      <a href="#" @click.prevent="section='accounting'" :class="{active:section==='accounting'}">
        <span x-show="lang==='en'">Accounting</span><span x-show="lang==='fr'">Comptabilité</span><span x-show="lang==='ar'">المحاسبة</span>
      </a>
      <a href="#" @click.prevent="section='users'" :class="{active:section==='users'}">
        <span x-show="lang==='en'">Users & Roles</span><span x-show="lang==='fr'">Utilisateurs</span><span x-show="lang==='ar'">المستخدمون</span>
      </a>
      <a href="#" @click.prevent="section='settings'" :class="{active:section==='settings'}">
        <span x-show="lang==='en'">Settings</span><span x-show="lang==='fr'">Paramètres</span><span x-show="lang==='ar'">الإعدادات</span>
      </a>
      <a href="#" @click.prevent="section='backup'" :class="{active:section==='backup'}">
        <span x-show="lang==='en'">Backup</span><span x-show="lang==='fr'">Sauvegarde</span><span x-show="lang==='ar'">النسخ الاحتياطي</span>
      </a>
      <a href="#" @click.prevent="section='pos'" :class="{active:section==='pos'}">POS</a>
      <a href="#" @click.prevent="section='sms'" :class="{active:section==='sms'}">
        <span x-show="lang==='en'">SMS / WhatsApp</span><span x-show="lang==='fr'">SMS / WhatsApp</span><span x-show="lang==='ar'">الرسائل</span>
      </a>
    </div>
  </div>

  {{-- Content --}}
  <div class="flex-1 min-w-0" :dir="lang==='ar'?'rtl':'ltr'">

    {{-- ═══ DASHBOARD ═══ --}}
    <div x-show="section==='dashboard'">
      {{-- EN --}}
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">The dashboard gives you a real-time overview of your business performance.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>KPI Cards</h4><p>The top row shows today's revenue, total sales, pending invoices, and low-stock alerts. Click any card to jump to the related module.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Sales Chart</h4><p>The line chart shows daily sales for the current month. Hover over a point to see the exact amount.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Recent Sales & Top Products</h4><p>The bottom tables list the 5 most recent sales and your best-selling products this month.</p></div></div>
          <div class="tip">💡 The accent color of the entire app is set in <strong>Settings → General → Invoice Color</strong>.</div>
        </div>
      </div>
      {{-- FR --}}
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Tableau de bord</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">Le tableau de bord affiche une vue en temps réel de vos performances commerciales.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Cartes KPI</h4><p>La première ligne affiche le chiffre d'affaires du jour, les ventes totales, les factures en attente et les alertes de stock bas.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Graphique des ventes</h4><p>Le graphique linéaire affiche les ventes quotidiennes du mois en cours. Survolez un point pour voir le montant exact.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Ventes récentes & Top produits</h4><p>Les tableaux du bas listent les 5 dernières ventes et vos produits les plus vendus ce mois-ci.</p></div></div>
          <div class="tip">💡 La couleur d'accentuation de toute l'application est définie dans <strong>Paramètres → Général → Couleur de facture</strong>.</div>
        </div>
      </div>
      {{-- AR --}}
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>لوحة التحكم</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">توفر لوحة التحكم نظرة عامة فورية على أداء عملك.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>بطاقات المؤشرات</h4><p>تعرض الصف العلوي إيرادات اليوم وإجمالي المبيعات والفواتير المعلقة وتنبيهات المخزون المنخفض.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>رسم بياني للمبيعات</h4><p>يعرض الرسم البياني المبيعات اليومية للشهر الحالي. مرر الماوس فوق نقطة لرؤية المبلغ الدقيق.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>آخر المبيعات وأفضل المنتجات</h4><p>تسرد الجداول السفلية آخر 5 مبيعات وأفضل المنتجات مبيعاً هذا الشهر.</p></div></div>
          <div class="tip">💡 لون التمييز في التطبيق يُضبط من <strong>الإعدادات ← عام ← لون الفاتورة</strong>.</div>
        </div>
      </div>
    </div>

    {{-- ═══ PRODUCTS ═══ --}}
    <div x-show="section==='products'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Products</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">Manage your product catalog with categories, brands, and units.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Create a Product</h4><p>Go to <strong>Products → All Products → + New Product</strong>. Fill in Name, SKU, Category, Brand, Unit, Selling Price, and Cost Price. Upload a product image if needed.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Set Stock & Alerts</h4><p>Enter the <strong>Opening Stock</strong> quantity and select the warehouse. Set a <strong>Minimum Stock</strong> level to trigger low-stock alerts automatically.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Categories</h4><p>Go to <strong>Products → Categories → + New</strong>. Give the category a name and optional parent category for nested grouping.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Brands & Units</h4><p>Add brands and measurement units in the same way from <strong>Products → Brands</strong> and <strong>Products → Units</strong>.</p></div></div>
          <div class="tip">💡 The SKU must be unique. Use a barcode scanner at the SKU field — it auto-fills.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Produits</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">Gérez votre catalogue produits avec catégories, marques et unités.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Créer un produit</h4><p>Allez dans <strong>Produits → Tous les produits → + Nouveau produit</strong>. Remplissez le nom, SKU, catégorie, marque, unité, prix de vente et prix de revient.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Stock & Alertes</h4><p>Saisissez la quantité de <strong>Stock initial</strong> et sélectionnez l'entrepôt. Définissez un <strong>Stock minimum</strong> pour déclencher des alertes automatiques.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Catégories</h4><p>Allez dans <strong>Produits → Catégories → + Nouveau</strong>. Donnez un nom et une catégorie parente optionnelle.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Marques & Unités</h4><p>Ajoutez marques et unités de mesure depuis <strong>Produits → Marques</strong> et <strong>Produits → Unités</strong>.</p></div></div>
          <div class="tip">💡 Le SKU doit être unique. Un scanner de code-barres peut remplir ce champ automatiquement.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المنتجات</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">إدارة كتالوج المنتجات مع الفئات والعلامات التجارية والوحدات.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إنشاء منتج</h4><p>اذهب إلى <strong>المنتجات ← جميع المنتجات ← + منتج جديد</strong>. أدخل الاسم ورمز SKU والفئة والعلامة التجارية والوحدة وسعر البيع وسعر التكلفة.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>المخزون والتنبيهات</h4><p>أدخل كمية <strong>المخزون الافتتاحي</strong> واختر المستودع. حدد <strong>الحد الأدنى للمخزون</strong> لتشغيل التنبيهات تلقائيًا.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>الفئات</h4><p>اذهب إلى <strong>المنتجات ← الفئات ← + جديد</strong>. أدخل الاسم وفئة رئيسية اختيارية للتصنيف المتداخل.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>العلامات التجارية والوحدات</h4><p>أضف العلامات التجارية ووحدات القياس من <strong>المنتجات ← العلامات التجارية</strong> و<strong>المنتجات ← الوحدات</strong>.</p></div></div>
          <div class="tip">💡 يجب أن يكون رمز SKU فريداً. يمكن استخدام ماسح الباركود لملء هذا الحقل تلقائياً.</div>
        </div>
      </div>
    </div>

    {{-- ═══ STOCK ═══ --}}
    <div x-show="section==='stock'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Stock Management</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Warehouses</h4><p>Go to <strong>Stock → Warehouses → + New</strong>. Enter warehouse name and address. Assign users who can access it.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Stock Adjustments</h4><p>Go to <strong>Stock → Adjustments → + New</strong>. Select warehouse, choose <strong>Add</strong> or <strong>Subtract</strong>, then add products and quantities. Add a reason note.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Stock Transfers</h4><p>Go to <strong>Stock → Transfers → + New</strong>. Select the <strong>From</strong> warehouse and the <strong>To</strong> warehouse, add products and quantities, then confirm.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Inventory Count</h4><p>Go to <strong>Stock → Inventory → Start Count</strong>. Scan or type each product quantity. The system calculates variance vs. system stock and updates automatically on completion.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Low-Stock Alerts</h4><p>Go to <strong>Stock → Alerts</strong> to see all products below their minimum stock level. Click a product to reorder.</p></div></div>
          <div class="warn">⚠️ Transfers deduct from the source warehouse immediately. Make sure quantities are correct before saving.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Gestion du Stock</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Entrepôts</h4><p>Allez dans <strong>Stock → Entrepôts → + Nouveau</strong>. Saisissez le nom et l'adresse. Assignez des utilisateurs autorisés.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Ajustements de stock</h4><p>Allez dans <strong>Stock → Ajustements → + Nouveau</strong>. Sélectionnez l'entrepôt, choisissez <strong>Ajouter</strong> ou <strong>Soustraire</strong>, puis ajoutez produits et quantités avec une note de raison.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Transferts de stock</h4><p>Allez dans <strong>Stock → Transferts → + Nouveau</strong>. Sélectionnez l'entrepôt source et destination, ajoutez les produits et confirmez.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Comptage d'inventaire</h4><p>Allez dans <strong>Stock → Inventaire → Démarrer le comptage</strong>. Scannez ou saisissez les quantités réelles. Le système calcule les écarts et met à jour automatiquement.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Alertes de stock bas</h4><p>Allez dans <strong>Stock → Alertes</strong> pour voir tous les produits sous leur seuil minimum.</p></div></div>
          <div class="warn">⚠️ Les transferts déduisent immédiatement de l'entrepôt source. Vérifiez les quantités avant d'enregistrer.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>إدارة المخزون</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>المستودعات</h4><p>اذهب إلى <strong>المخزون ← المستودعات ← + جديد</strong>. أدخل الاسم والعنوان وعيّن المستخدمين المصرح لهم.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>تسويات المخزون</h4><p>اذهب إلى <strong>المخزون ← التسويات ← + جديد</strong>. اختر المستودع، حدد <strong>إضافة</strong> أو <strong>طرح</strong>، ثم أضف المنتجات والكميات مع ملاحظة السبب.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تحويلات المخزون</h4><p>اذهب إلى <strong>المخزون ← التحويلات ← + جديد</strong>. اختر المستودع المصدر والوجهة، أضف المنتجات والكميات ثم أكّد.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>جرد المخزون</h4><p>اذهب إلى <strong>المخزون ← الجرد ← بدء العدّ</strong>. امسح أو أدخل الكميات الفعلية. يحسب النظام الفرق ويحدّث تلقائياً.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>تنبيهات المخزون المنخفض</h4><p>اذهب إلى <strong>المخزون ← التنبيهات</strong> لعرض المنتجات التي تقل عن الحد الأدنى.</p></div></div>
          <div class="warn">⚠️ تُخصم التحويلات من المستودع المصدر فوراً. تحقق من الكميات قبل الحفظ.</div>
        </div>
      </div>
    </div>

    {{-- ═══ PURCHASES ═══ --}}
    <div x-show="section==='purchases'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Purchases</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Add a Supplier</h4><p>Go to <strong>Purchases → Suppliers → + New</strong>. Enter name, phone, email, address and opening balance.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Create a Purchase Order</h4><p>Go to <strong>Purchases → Purchase Orders → + New</strong>. Select supplier and warehouse, add product lines (product, qty, unit cost), then choose payment status (Paid / Partial / Unpaid).</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Record a Payment</h4><p>Open the purchase order, click <strong>Add Payment</strong>. Enter the amount, payment method, and date. The due balance updates automatically.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Purchase Returns</h4><p>Go to <strong>Purchases → Returns → + New</strong>. Select the original purchase order, choose the items to return and quantities. Stock is deducted automatically.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Supplier Due Report</h4><p>Go to <strong>Purchases → Supplier Due</strong> to see outstanding balances per supplier.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Achats</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Ajouter un fournisseur</h4><p>Allez dans <strong>Achats → Fournisseurs → + Nouveau</strong>. Saisissez nom, téléphone, email, adresse et solde initial.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Créer un bon de commande</h4><p>Allez dans <strong>Achats → Bons de commande → + Nouveau</strong>. Sélectionnez le fournisseur et l'entrepôt, ajoutez les lignes produits, puis choisissez le statut de paiement.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Enregistrer un paiement</h4><p>Ouvrez la commande d'achat, cliquez sur <strong>Ajouter un paiement</strong>. Le solde dû se met à jour automatiquement.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Retours d'achats</h4><p>Allez dans <strong>Achats → Retours → + Nouveau</strong>. Sélectionnez le bon de commande d'origine et les articles à retourner.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Soldes fournisseurs</h4><p>Allez dans <strong>Achats → Soldes fournisseurs</strong> pour voir les montants dus par fournisseur.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المشتريات</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إضافة مورّد</h4><p>اذهب إلى <strong>المشتريات ← الموردون ← + جديد</strong>. أدخل الاسم والهاتف والبريد الإلكتروني والعنوان والرصيد الافتتاحي.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>إنشاء أمر شراء</h4><p>اذهب إلى <strong>المشتريات ← أوامر الشراء ← + جديد</strong>. اختر المورّد والمستودع وأضف بنود المنتجات، ثم اختر حالة الدفع.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تسجيل دفعة</h4><p>افتح أمر الشراء وانقر على <strong>إضافة دفعة</strong>. أدخل المبلغ وطريقة الدفع والتاريخ. يتحدث الرصيد المستحق تلقائياً.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>مرتجعات المشتريات</h4><p>اذهب إلى <strong>المشتريات ← المرتجعات ← + جديد</strong>. اختر أمر الشراء الأصلي وحدد الأصناف المراد إرجاعها.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>تقرير مديونية الموردين</h4><p>اذهب إلى <strong>المشتريات ← مديونية الموردين</strong> لعرض الأرصدة المستحقة لكل مورّد.</p></div></div>
        </div>
      </div>
    </div>

    {{-- ═══ SALES ═══ --}}
    <div x-show="section==='sales'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Sales</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Create a Sale</h4><p>Go to <strong>Sales → Sales Orders → + New Sale</strong>. Select or create a customer, choose warehouse, add product lines with quantities and prices, apply discount or tax if needed, then save.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Payment Status</h4><p>Set status to <strong>Paid</strong>, <strong>Partial</strong>, or <strong>Unpaid</strong>. For partial, enter the amount received. The balance appears on the customer's account.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Print / Send Invoice</h4><p>Open the sale, click <strong>Print</strong> for a PDF or <strong>Send Email</strong> to deliver it to the customer automatically (requires Email SMTP configured).</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Sales Returns</h4><p>Go to <strong>Sales → Returns → + New</strong>. Select the original sale, choose items and quantities. Stock is returned to the warehouse automatically.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Customer Due</h4><p>Go to <strong>Sales → Customer Due</strong> to see unpaid balances per customer.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Ventes</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Créer une vente</h4><p>Allez dans <strong>Ventes → Commandes de vente → + Nouvelle vente</strong>. Sélectionnez ou créez un client, choisissez l'entrepôt, ajoutez les lignes produits, appliquez remise ou taxe, puis enregistrez.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Statut de paiement</h4><p>Définissez le statut sur <strong>Payé</strong>, <strong>Partiel</strong> ou <strong>Impayé</strong>. Pour un paiement partiel, saisissez le montant reçu.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Imprimer / Envoyer la facture</h4><p>Ouvrez la vente, cliquez sur <strong>Imprimer</strong> pour un PDF ou <strong>Envoyer par email</strong> (nécessite la configuration SMTP).</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Retours de ventes</h4><p>Allez dans <strong>Ventes → Retours → + Nouveau</strong>. Sélectionnez la vente d'origine et les articles à retourner.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Soldes clients</h4><p>Allez dans <strong>Ventes → Soldes clients</strong> pour voir les montants dus.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المبيعات</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إنشاء بيع</h4><p>اذهب إلى <strong>المبيعات ← أوامر البيع ← + بيع جديد</strong>. اختر أو أنشئ عميلاً، اختر المستودع وأضف بنود المنتجات مع الكميات والأسعار، ثم احفظ.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>حالة الدفع</h4><p>اضبط الحالة على <strong>مدفوع</strong> أو <strong>جزئي</strong> أو <strong>غير مدفوع</strong>. للدفع الجزئي أدخل المبلغ المستلم.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>طباعة / إرسال الفاتورة</h4><p>افتح البيع وانقر على <strong>طباعة</strong> للحصول على PDF أو <strong>إرسال بريد إلكتروني</strong> (يتطلب تهيئة SMTP).</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>مرتجعات المبيعات</h4><p>اذهب إلى <strong>المبيعات ← المرتجعات ← + جديد</strong>. اختر البيع الأصلي وحدد الأصناف المراد إرجاعها.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>مديونية العملاء</h4><p>اذهب إلى <strong>المبيعات ← مديونية العملاء</strong> لعرض الأرصدة غير المدفوعة.</p></div></div>
        </div>
      </div>
    </div>

    {{-- ═══ INVOICES ═══ --}}
    <div x-show="section==='invoices'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Invoices & Quotations</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Create a Quotation</h4><p>Go to <strong>Sales → Quotations → + New</strong>. Add customer, validity date, products and prices. Save as draft or send to customer by email.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Convert to Sale</h4><p>Open the quotation and click <strong>Convert to Sale</strong>. All items are copied automatically. Review and confirm.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Create an Invoice</h4><p>Go to <strong>Sales → Invoices → + New</strong>. Link it to a sale or create standalone. Set due date and payment terms.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Auto-Send on Creation</h4><p>Enable <strong>Settings → Notifications → Auto-Send Invoice to Client</strong> so invoices are emailed automatically when a sale is created.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Recurring Invoices</h4><p>When creating a sale, enable the <strong>Recurring</strong> option and set the interval (weekly, monthly). The system generates the next invoice automatically.</p></div></div>
          <div class="tip">💡 Invoice color and logo are set in <strong>Settings → General</strong>.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Factures & Devis</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Créer un devis</h4><p>Allez dans <strong>Ventes → Devis → + Nouveau</strong>. Ajoutez le client, la date de validité, les produits et prix. Enregistrez en brouillon ou envoyez par email.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Convertir en vente</h4><p>Ouvrez le devis et cliquez sur <strong>Convertir en vente</strong>. Tous les articles sont copiés automatiquement.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Créer une facture</h4><p>Allez dans <strong>Ventes → Factures → + Nouveau</strong>. Liez-la à une vente ou créez-la indépendamment avec une date d'échéance.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Envoi automatique</h4><p>Activez <strong>Paramètres → Notifications → Auto-envoi facture client</strong> pour envoyer les factures automatiquement à la création.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Factures récurrentes</h4><p>Lors de la création d'une vente, activez l'option <strong>Récurrent</strong> et définissez l'intervalle (hebdomadaire, mensuel).</p></div></div>
          <div class="tip">💡 La couleur et le logo des factures se configurent dans <strong>Paramètres → Général</strong>.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>الفواتير وعروض الأسعار</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إنشاء عرض سعر</h4><p>اذهب إلى <strong>المبيعات ← عروض الأسعار ← + جديد</strong>. أضف العميل وتاريخ الصلاحية والمنتجات والأسعار. احفظه كمسودة أو أرسله بالبريد الإلكتروني.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>تحويل إلى بيع</h4><p>افتح عرض السعر وانقر على <strong>تحويل إلى بيع</strong>. تُنسخ جميع البنود تلقائياً.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>إنشاء فاتورة</h4><p>اذهب إلى <strong>المبيعات ← الفواتير ← + جديد</strong>. ربطها ببيع أو إنشاؤها مستقلة مع تاريخ الاستحقاق.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>الإرسال التلقائي</h4><p>فعّل <strong>الإعدادات ← الإشعارات ← إرسال الفاتورة تلقائياً للعميل</strong> لإرسال الفواتير عند الإنشاء.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>الفواتير المتكررة</h4><p>عند إنشاء بيع، فعّل خيار <strong>متكرر</strong> واضبط الفاصل الزمني. يُنشئ النظام الفاتورة التالية تلقائياً.</p></div></div>
          <div class="tip">💡 يُضبط لون وشعار الفاتورة من <strong>الإعدادات ← عام</strong>.</div>
        </div>
      </div>
    </div>

    {{-- ═══ PAYMENTS ═══ --}}
    <div x-show="section==='payments'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Payments</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Record a Payment</h4><p>Go to <strong>Payments → + New Payment</strong>. Select the sale or purchase, enter amount, choose method (cash, card, bank transfer, cheque) and date.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>View Payment History</h4><p>Go to <strong>Payments</strong> to see all transactions. Filter by date range, customer, or payment method. Export to CSV.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Stripe Online Payment</h4><p>If Stripe is configured in settings, share the payment link with the customer. They pay online and the sale is marked paid automatically.</p></div></div>
          <div class="tip">💡 Partial payments create a payment record and reduce the outstanding balance on the order.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Paiements</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Enregistrer un paiement</h4><p>Allez dans <strong>Paiements → + Nouveau paiement</strong>. Sélectionnez la vente ou l'achat, saisissez le montant, choisissez la méthode et la date.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Historique des paiements</h4><p>Allez dans <strong>Paiements</strong> pour voir toutes les transactions. Filtrez par période, client ou méthode de paiement. Exportez en CSV.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Paiement en ligne Stripe</h4><p>Si Stripe est configuré, partagez le lien de paiement avec le client. Il paie en ligne et la vente est automatiquement marquée comme payée.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المدفوعات</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>تسجيل دفعة</h4><p>اذهب إلى <strong>المدفوعات ← + دفعة جديدة</strong>. اختر البيع أو الشراء، أدخل المبلغ وطريقة الدفع والتاريخ.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>سجل المدفوعات</h4><p>اذهب إلى <strong>المدفوعات</strong> لعرض جميع المعاملات. فلتر حسب الفترة أو العميل أو طريقة الدفع. صدّر إلى CSV.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>الدفع عبر الإنترنت Stripe</h4><p>إذا كان Stripe مُهيّأً، شارك رابط الدفع مع العميل. يدفع عبر الإنترنت ويُعلَّم البيع مدفوعاً تلقائياً.</p></div></div>
        </div>
      </div>
    </div>

    {{-- ═══ EXPENSES ═══ --}}
    <div x-show="section==='expenses'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Expenses</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Create Expense Categories</h4><p>Go to <strong>Expenses → Categories → + New</strong>. Add categories like Rent, Utilities, Salaries, etc.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Record an Expense</h4><p>Go to <strong>Expenses → + New Expense</strong>. Select category and warehouse, enter amount, date, and reference. Attach a receipt image if needed.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>View & Filter</h4><p>All expenses are listed with filters by category, warehouse, and date range. Export to CSV for accounting purposes.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Dépenses</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Créer des catégories</h4><p>Allez dans <strong>Dépenses → Catégories → + Nouveau</strong>. Ajoutez des catégories comme Loyer, Charges, Salaires, etc.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Enregistrer une dépense</h4><p>Allez dans <strong>Dépenses → + Nouvelle dépense</strong>. Sélectionnez la catégorie et l'entrepôt, saisissez le montant, la date et la référence.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Consulter & Filtrer</h4><p>Toutes les dépenses sont listées avec des filtres par catégorie, entrepôt et période. Exportez en CSV.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المصروفات</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إنشاء فئات المصروفات</h4><p>اذهب إلى <strong>المصروفات ← الفئات ← + جديد</strong>. أضف فئات كالإيجار والمرافق والرواتب وغيرها.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>تسجيل مصروف</h4><p>اذهب إلى <strong>المصروفات ← + مصروف جديد</strong>. اختر الفئة والمستودع، أدخل المبلغ والتاريخ والمرجع.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>عرض وتصفية</h4><p>تُدرج جميع المصروفات مع مرشحات حسب الفئة والمستودع والفترة. صدّر إلى CSV.</p></div></div>
        </div>
      </div>
    </div>

    {{-- ═══ CUSTOMERS ═══ --}}
    <div x-show="section==='customers'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Customers</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Add a Customer</h4><p>Go to <strong>Sales → Customers → + New</strong>. Enter name, phone, email, address, and opening balance.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Customer Profile</h4><p>Click a customer name to view their full sales history, payment records, outstanding balance, and contact details.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Customer Report</h4><p>Go to <strong>Reports → Top Customers</strong> to rank by total spend. Go to <strong>Reports → Customer Detail</strong> to see one customer's full history.</p></div></div>
          <div class="tip">💡 SMS/WhatsApp notifications can be sent to customers on sale creation, payment, and overdue reminders — configure in Settings → SMS.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Clients</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Ajouter un client</h4><p>Allez dans <strong>Ventes → Clients → + Nouveau</strong>. Saisissez nom, téléphone, email, adresse et solde initial.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Profil client</h4><p>Cliquez sur le nom du client pour voir son historique complet, ses paiements et son solde impayé.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Rapport client</h4><p>Allez dans <strong>Rapports → Meilleurs clients</strong> pour un classement par dépenses. Voir <strong>Rapports → Détail client</strong> pour l'historique complet.</p></div></div>
          <div class="tip">💡 Des notifications SMS/WhatsApp peuvent être envoyées aux clients lors de la création de vente, paiement, ou rappels d'impayés.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>العملاء</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إضافة عميل</h4><p>اذهب إلى <strong>المبيعات ← العملاء ← + جديد</strong>. أدخل الاسم والهاتف والبريد الإلكتروني والعنوان والرصيد الافتتاحي.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>ملف العميل</h4><p>انقر على اسم العميل لعرض سجل مبيعاته الكامل وسجلات الدفع والرصيد المستحق.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تقرير العملاء</h4><p>اذهب إلى <strong>التقارير ← أفضل العملاء</strong> للترتيب حسب الإنفاق. اذهب إلى <strong>التقارير ← تفاصيل العميل</strong> للسجل الكامل.</p></div></div>
          <div class="tip">💡 يمكن إرسال إشعارات SMS/واتساب للعملاء عند إنشاء البيع والدفع وتذكيرات التأخر.</div>
        </div>
      </div>
    </div>

    {{-- ═══ REPORTS ═══ --}}
    <div x-show="section==='reports'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Reports</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">All reports support date-range filtering and CSV export.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Sales Report</h4><p><strong>Reports → Sales</strong> — KPIs (revenue, orders, avg order, growth), chart by day, top products table.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Financial Report</h4><p><strong>Reports → Financial</strong> — Income, expenses, net profit, charts. Use for monthly P&L review.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Stock Report</h4><p><strong>Reports → Stock</strong> — Current quantities, value, and movements. Filter by warehouse.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Inventory Valuation</h4><p><strong>Reports → Inventory Valuation</strong> — Cost value vs. sell value of current stock. Filter by warehouse.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>By-Category / Brand / User / Warehouse</h4><p>Drill-down reports under <strong>Reports</strong> let you break sales, purchases, and quotations by any dimension.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><h4>Email & SMS Logs</h4><p><strong>Reports → Email Log</strong> and <strong>Reports → SMS Log</strong> — Track every notification sent with status (sent/failed) and error details.</p></div></div>
          <div class="tip">💡 Click <strong>Export CSV</strong> on any report to download the data for Excel analysis.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Rapports</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">Tous les rapports supportent le filtre par période et l'export CSV.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Rapport des ventes</h4><p><strong>Rapports → Ventes</strong> — KPIs (CA, commandes, panier moyen, croissance), graphique journalier, top produits.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Rapport financier</h4><p><strong>Rapports → Financier</strong> — Revenus, dépenses, bénéfice net, graphiques. Idéal pour la revue mensuelle P&L.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Rapport de stock</h4><p><strong>Rapports → Stock</strong> — Quantités actuelles, valeur et mouvements. Filtre par entrepôt.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Valorisation du stock</h4><p><strong>Rapports → Valorisation inventaire</strong> — Valeur au coût vs. valeur de vente du stock actuel.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Par catégorie / marque / utilisateur</h4><p>Les rapports détaillés permettent de décomposer ventes et achats par toute dimension.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><h4>Journaux Email & SMS</h4><p><strong>Rapports → Journal Email</strong> et <strong>Journal SMS</strong> — Suivez chaque notification avec statut et détails d'erreur.</p></div></div>
          <div class="tip">💡 Cliquez sur <strong>Exporter CSV</strong> sur n'importe quel rapport pour télécharger les données.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>التقارير</h3>
          <p style="font-size:.875rem;color:#475569;margin-bottom:1rem">جميع التقارير تدعم التصفية بالتاريخ والتصدير إلى CSV.</p>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>تقرير المبيعات</h4><p><strong>التقارير ← المبيعات</strong> — مؤشرات الأداء (الإيرادات، الطلبات، متوسط الطلب، النمو)، رسم بياني يومي، أفضل المنتجات.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>التقرير المالي</h4><p><strong>التقارير ← المالي</strong> — الإيرادات والمصروفات وصافي الربح والرسوم البيانية.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تقرير المخزون</h4><p><strong>التقارير ← المخزون</strong> — الكميات الحالية والقيمة والحركات. تصفية حسب المستودع.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>تقييم المخزون</h4><p><strong>التقارير ← تقييم المخزون</strong> — قيمة التكلفة مقابل قيمة البيع للمخزون الحالي.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>حسب الفئة / العلامة / المستخدم</h4><p>التقارير التفصيلية تتيح تفصيل المبيعات والمشتريات حسب أي بُعد.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><h4>سجلات البريد والرسائل</h4><p><strong>التقارير ← سجل البريد</strong> و<strong>سجل الرسائل</strong> — تتبع كل إشعار مُرسل مع الحالة وتفاصيل الخطأ.</p></div></div>
          <div class="tip">💡 انقر على <strong>تصدير CSV</strong> في أي تقرير لتنزيل البيانات.</div>
        </div>
      </div>
    </div>

    {{-- ═══ ACCOUNTING ═══ --}}
    <div x-show="section==='accounting'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Accounting</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Chart of Accounts</h4><p>Go to <strong>Accounting → Accounts</strong>. Create accounts (Cash, Bank, Revenue, Expense, etc.) with type and opening balance.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Manual Transactions</h4><p>Go to <strong>Accounting → Transactions → + New</strong>. Choose account, type (debit/credit), amount, and date.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Journal Entries</h4><p>Go to <strong>Accounting → Journals</strong> to view double-entry records created automatically from sales, purchases, and payments.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Tax / VAT</h4><p>Go to <strong>Accounting → Tax</strong>. View taxable transactions and generate a VAT declaration for a selected period.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Comptabilité</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Plan comptable</h4><p>Allez dans <strong>Comptabilité → Comptes</strong>. Créez des comptes (Caisse, Banque, Revenus, Charges, etc.) avec type et solde initial.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Transactions manuelles</h4><p>Allez dans <strong>Comptabilité → Transactions → + Nouveau</strong>. Choisissez le compte, le type (débit/crédit), le montant et la date.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Écritures comptables</h4><p>Allez dans <strong>Comptabilité → Journaux</strong> pour voir les écritures en partie double générées automatiquement.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>TVA</h4><p>Allez dans <strong>Comptabilité → TVA</strong>. Consultez les transactions taxables et générez une déclaration de TVA.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المحاسبة</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>دليل الحسابات</h4><p>اذهب إلى <strong>المحاسبة ← الحسابات</strong>. أنشئ حسابات (نقدية، بنك، إيرادات، مصروفات) مع النوع والرصيد الافتتاحي.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>المعاملات اليدوية</h4><p>اذهب إلى <strong>المحاسبة ← المعاملات ← + جديد</strong>. اختر الحساب والنوع (مدين/دائن) والمبلغ والتاريخ.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>القيود المحاسبية</h4><p>اذهب إلى <strong>المحاسبة ← القيود</strong> لعرض قيود القيد المزدوج المُنشأة تلقائياً من المبيعات والمشتريات.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>الضريبة / ضريبة القيمة المضافة</h4><p>اذهب إلى <strong>المحاسبة ← الضريبة</strong>. اعرض المعاملات الخاضعة للضريبة وأنشئ إقراراً ضريبياً.</p></div></div>
        </div>
      </div>
    </div>

    {{-- ═══ USERS ═══ --}}
    <div x-show="section==='users'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Users & Roles</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Create a User</h4><p>Go to <strong>Users → + New User</strong>. Enter first name, last name, email, and password. Assign a role.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Create a Role</h4><p>Go to <strong>Users → Roles & Permissions → + New Role</strong>. Give the role a name, then check the permissions you want to grant (view, create, edit, delete per module).</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Assign Role to User</h4><p>Edit a user and change their role in the <strong>Role</strong> dropdown. Changes take effect immediately on next login.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Two-Factor Authentication</h4><p>Go to <strong>Settings → Security</strong>. Enable 2FA — a QR code appears to scan with Google Authenticator. Enter the 6-digit code to confirm.</p></div></div>
          <div class="warn">⚠️ The Admin role has all permissions. Create limited roles for cashiers and stock clerks.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Utilisateurs & Rôles</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Créer un utilisateur</h4><p>Allez dans <strong>Utilisateurs → + Nouvel utilisateur</strong>. Saisissez prénom, nom, email et mot de passe. Assignez un rôle.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Créer un rôle</h4><p>Allez dans <strong>Utilisateurs → Rôles & Permissions → + Nouveau rôle</strong>. Donnez un nom et cochez les permissions souhaitées par module.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Assigner un rôle</h4><p>Modifiez un utilisateur et changez son rôle dans la liste déroulante. Les changements prennent effet à la prochaine connexion.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Authentification à deux facteurs</h4><p>Allez dans <strong>Paramètres → Sécurité</strong>. Activez le 2FA — un QR code apparaît à scanner avec Google Authenticator.</p></div></div>
          <div class="warn">⚠️ Le rôle Admin dispose de toutes les permissions. Créez des rôles limités pour les caissiers et magasiniers.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>المستخدمون والأدوار</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>إنشاء مستخدم</h4><p>اذهب إلى <strong>المستخدمون ← + مستخدم جديد</strong>. أدخل الاسم الأول والأخير والبريد الإلكتروني وكلمة المرور. عيّن دوراً.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>إنشاء دور</h4><p>اذهب إلى <strong>المستخدمون ← الأدوار والصلاحيات ← + دور جديد</strong>. أعطِ الدور اسماً ثم حدد الصلاحيات المطلوبة لكل وحدة.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تعيين دور للمستخدم</h4><p>عدّل المستخدم وغيّر دوره من القائمة المنسدلة. تسري التغييرات عند تسجيل الدخول التالي.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>المصادقة الثنائية</h4><p>اذهب إلى <strong>الإعدادات ← الأمان</strong>. فعّل المصادقة الثنائية — يظهر رمز QR لمسحه بتطبيق Google Authenticator.</p></div></div>
          <div class="warn">⚠️ دور المسؤول يمتلك جميع الصلاحيات. أنشئ أدواراً محدودة للكاشيرين وأمناء المستودعات.</div>
        </div>
      </div>
    </div>

    {{-- ═══ SETTINGS ═══ --}}
    <div x-show="section==='settings'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Settings</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>General Settings</h4><p>Go to <strong>Settings → General</strong>. Set company name, logo, address, currency, date format, and the accent color used across all invoices and the app.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Email SMTP</h4><p>Go to <strong>Settings → Email SMTP</strong>. Enter your mail server host, port, encryption, username, password, and sender name. Click <strong>Send Test Email</strong> to verify.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Tax Settings</h4><p>Go to <strong>Settings → Tax</strong>. Set the default tax rate and create multiple named rates (VAT 20%, VAT 7%, etc.). Enable or disable tax on invoices globally.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Notifications</h4><p>Go to <strong>Settings → Notifications</strong>. Enable auto-send of invoices/quotations to clients. Configure which events trigger admin email alerts.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Language</h4><p>Go to <strong>Settings → Languages</strong>. Set the application language (EN/FR/AR) and text direction (LTR/RTL). Toggle active languages for multi-language support.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><h4>POS Settings</h4><p>Go to <strong>Settings → POS</strong>. Choose receipt size (A4/Thermal), set default warehouse, customer, and payment method for point-of-sale sessions.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Paramètres</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Paramètres généraux</h4><p>Allez dans <strong>Paramètres → Général</strong>. Définissez le nom de l'entreprise, le logo, l'adresse, la devise, le format de date et la couleur d'accentuation.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Email SMTP</h4><p>Allez dans <strong>Paramètres → Email SMTP</strong>. Saisissez hôte, port, chiffrement, identifiant, mot de passe et nom d'expéditeur. Cliquez sur <strong>Envoyer un email test</strong>.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Paramètres de taxe</h4><p>Allez dans <strong>Paramètres → Taxe</strong>. Définissez le taux par défaut et créez plusieurs taux nommés (TVA 20%, TVA 7%, etc.).</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Notifications</h4><p>Allez dans <strong>Paramètres → Notifications</strong>. Activez l'envoi automatique des factures et devis aux clients. Configurez les alertes admin par email.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Langue</h4><p>Allez dans <strong>Paramètres → Langues</strong>. Définissez la langue (FR/EN/AR) et la direction du texte (LTR/RTL).</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><h4>Paramètres POS</h4><p>Allez dans <strong>Paramètres → POS</strong>. Choisissez la taille de ticket (A4/Thermique), l'entrepôt, client et mode de paiement par défaut.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>الإعدادات</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>الإعدادات العامة</h4><p>اذهب إلى <strong>الإعدادات ← عام</strong>. اضبط اسم الشركة والشعار والعنوان والعملة وصيغة التاريخ ولون التمييز.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>إعدادات SMTP</h4><p>اذهب إلى <strong>الإعدادات ← SMTP البريد</strong>. أدخل المضيف والمنفذ والتشفير واسم المستخدم وكلمة المرور. انقر على <strong>إرسال بريد تجريبي</strong>.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>إعدادات الضريبة</h4><p>اذهب إلى <strong>الإعدادات ← الضريبة</strong>. اضبط المعدل الافتراضي وأنشئ معدلات متعددة مسماة.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>الإشعارات</h4><p>اذهب إلى <strong>الإعدادات ← الإشعارات</strong>. فعّل الإرسال التلقائي للفواتير والعروض للعملاء. اضبط تنبيهات البريد الإلكتروني للمسؤول.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>اللغة</h4><p>اذهب إلى <strong>الإعدادات ← اللغات</strong>. اضبط لغة التطبيق (عربي/فرنسي/إنجليزي) واتجاه النص.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><h4>إعدادات نقطة البيع</h4><p>اذهب إلى <strong>الإعدادات ← نقطة البيع</strong>. اختر حجم الإيصال والمستودع والعميل وطريقة الدفع الافتراضية.</p></div></div>
        </div>
      </div>
    </div>

    {{-- ═══ BACKUP ═══ --}}
    <div x-show="section==='backup'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Backup & Restore</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Manual Backup</h4><p>Go to <strong>Settings → Backup</strong>. Enter a backup name (or leave the auto-generated timestamp) and click <strong>Create Backup Now</strong>. The file appears in the list within seconds.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Download a Backup</h4><p>In the backup list, click the <strong>download icon</strong> (↓) next to any backup file. The compressed <code>.sql.gz</code> file is downloaded to your computer.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Delete Old Backups</h4><p>Click the <strong>trash icon</strong> next to any backup and confirm. The file is permanently deleted from the server.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Automatic Daily Backup</h4><p>In the <strong>Schedule</strong> section, enable <strong>Auto Backup</strong>, set frequency (daily/weekly/monthly), time (default 02:00), and retention in days. Click <strong>Save Schedule</strong>. The system scheduler runs at the configured time each day.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Restore from Backup</h4><p>Download the <code>.sql.gz</code> file, decompress it to get the <code>.sql</code> file, then import it using phpMyAdmin or run: <code>mysql -u root -p database_name &lt; backup.sql</code></p></div></div>
          <div class="tip">💡 Backup files are stored in <code>storage/app/backups/</code> on the server. Keep regular copies in a safe off-site location.</div>
          <div class="warn">⚠️ The scheduler requires a system cron job: <code>* * * * * php /path/to/artisan schedule:run</code> — ask your hosting provider to set this up.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Sauvegarde & Restauration</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Sauvegarde manuelle</h4><p>Allez dans <strong>Paramètres → Sauvegarde</strong>. Saisissez un nom (ou laissez l'horodatage automatique) et cliquez sur <strong>Créer une sauvegarde maintenant</strong>. Le fichier apparaît dans la liste en quelques secondes.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Télécharger une sauvegarde</h4><p>Dans la liste des sauvegardes, cliquez sur l'<strong>icône de téléchargement</strong> (↓). Le fichier compressé <code>.sql.gz</code> est téléchargé sur votre ordinateur.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Supprimer une sauvegarde</h4><p>Cliquez sur l'<strong>icône poubelle</strong> et confirmez. Le fichier est définitivement supprimé du serveur.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Sauvegarde automatique quotidienne</h4><p>Dans la section <strong>Planification</strong>, activez <strong>Sauvegarde automatique</strong>, définissez la fréquence (quotidienne/hebdomadaire/mensuelle), l'heure (défaut 02h00) et la rétention en jours. Cliquez sur <strong>Enregistrer la planification</strong>.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Restaurer depuis une sauvegarde</h4><p>Téléchargez le fichier <code>.sql.gz</code>, décompressez-le pour obtenir le <code>.sql</code>, puis importez-le via phpMyAdmin ou : <code>mysql -u root -p nom_base &lt; backup.sql</code></p></div></div>
          <div class="tip">💡 Les fichiers sont stockés dans <code>storage/app/backups/</code>. Conservez des copies régulières hors site.</div>
          <div class="warn">⚠️ Le planificateur nécessite une tâche cron système : <code>* * * * * php /chemin/vers/artisan schedule:run</code></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>النسخ الاحتياطي والاستعادة</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>نسخ احتياطي يدوي</h4><p>اذهب إلى <strong>الإعدادات ← النسخ الاحتياطي</strong>. أدخل اسماً أو اترك الطابع الزمني التلقائي، وانقر على <strong>إنشاء نسخة احتياطية الآن</strong>. يظهر الملف في القائمة خلال ثوانٍ.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>تنزيل نسخة احتياطية</h4><p>في قائمة النسخ الاحتياطية، انقر على <strong>أيقونة التنزيل</strong> (↓) بجانب أي ملف. يُنزّل ملف <code>.sql.gz</code> المضغوط إلى جهازك.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>حذف نسخة احتياطية</h4><p>انقر على <strong>أيقونة الحذف</strong> وأكّد. يُحذف الملف نهائياً من الخادم.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>النسخ الاحتياطي اليومي التلقائي</h4><p>في قسم <strong>الجدول الزمني</strong>، فعّل <strong>النسخ الاحتياطي التلقائي</strong> وحدد التكرار (يومي/أسبوعي/شهري) والوقت (الافتراضي 02:00) وأيام الاحتفاظ. انقر على <strong>حفظ الجدول</strong>.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>الاستعادة من نسخة احتياطية</h4><p>نزّل ملف <code>.sql.gz</code>، فكّ ضغطه للحصول على <code>.sql</code>، ثم استورده عبر phpMyAdmin أو: <code>mysql -u root -p اسم_قاعدة_البيانات &lt; backup.sql</code></p></div></div>
          <div class="tip">💡 تُحفظ الملفات في <code>storage/app/backups/</code>. احتفظ بنسخ منتظمة في موقع آمن خارج الخادم.</div>
          <div class="warn">⚠️ يتطلب الجدولة مهمة cron: <code>* * * * * php /المسار/إلى/artisan schedule:run</code></div>
        </div>
      </div>
    </div>

    {{-- ═══ POS ═══ --}}
    <div x-show="section==='pos'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>Point of Sale (POS)</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Open a POS Session</h4><p>Go to <strong>Sales → POS</strong>. The POS screen loads with a product grid on the left and the cart on the right.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Add Products</h4><p>Click a product tile to add it to the cart, or scan its barcode. Adjust quantity using + / − buttons or type directly in the quantity field.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Apply Discount</h4><p>Click the discount field in the cart line or apply a global discount at the bottom of the cart.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Complete the Sale</h4><p>Click <strong>Charge</strong>. Select payment method (cash, card, etc.), enter amount received, and confirm. The receipt prints or displays automatically.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Configure POS</h4><p>Go to <strong>Settings → POS</strong> to set default warehouse, receipt size, and whether to require customer selection.</p></div></div>
          <div class="tip">💡 POS works offline-friendly. Sales are saved as drafts if the connection drops and synced when restored.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Point de Vente (POS)</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Ouvrir une session POS</h4><p>Allez dans <strong>Ventes → POS</strong>. L'écran affiche une grille de produits à gauche et le panier à droite.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Ajouter des produits</h4><p>Cliquez sur une tuile produit ou scannez son code-barres. Ajustez la quantité avec + / − ou saisissez-la directement.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Appliquer une remise</h4><p>Cliquez sur le champ remise dans la ligne du panier ou appliquez une remise globale en bas du panier.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Finaliser la vente</h4><p>Cliquez sur <strong>Encaisser</strong>. Sélectionnez le mode de paiement, saisissez le montant reçu et confirmez. Le ticket s'imprime automatiquement.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Configurer le POS</h4><p>Allez dans <strong>Paramètres → POS</strong> pour définir l'entrepôt par défaut, la taille du ticket et les options de sélection client.</p></div></div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>نقطة البيع (POS)</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>فتح جلسة POS</h4><p>اذهب إلى <strong>المبيعات ← نقطة البيع</strong>. تظهر شبكة المنتجات على اليسار وعربة التسوق على اليمين.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>إضافة منتجات</h4><p>انقر على بلاطة المنتج أو امسح الباركود. اضبط الكمية بأزرار + / − أو اكتب مباشرة.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تطبيق خصم</h4><p>انقر على حقل الخصم في بند العربة أو طبّق خصماً عاماً في أسفل العربة.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>إتمام البيع</h4><p>انقر على <strong>تحصيل</strong>. اختر طريقة الدفع، أدخل المبلغ المستلم وأكّد. يُطبع الإيصال تلقائياً.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>إعداد نقطة البيع</h4><p>اذهب إلى <strong>الإعدادات ← نقطة البيع</strong> لضبط المستودع الافتراضي وحجم الإيصال وخيارات اختيار العميل.</p></div></div>
          <div class="tip">💡 تعمل نقطة البيع بشكل صديق للاتصال المنقطع. تُحفظ المبيعات كمسودات وتُزامن عند استعادة الاتصال.</p></div>
        </div>
      </div>
    </div>

    {{-- ═══ SMS ═══ --}}
    <div x-show="section==='sms'">
      <div x-show="lang==='en'">
        <div class="doc-card">
          <h3>SMS & WhatsApp Notifications</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Choose a Gateway</h4><p>Go to <strong>Settings → SMS / WhatsApp</strong>. Select your gateway: <strong>Twilio</strong>, <strong>Nexmo (Vonage)</strong>, <strong>Infobip</strong>, <strong>Termii</strong>, or <strong>WhatsApp</strong>. Enter the credentials (Account SID, Auth Token, etc.) for the chosen gateway.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Enable Event Triggers</h4><p>Toggle which events send an SMS: <em>Sale Created</em>, <em>Payment Received</em>, <em>Invoice Overdue</em>, <em>Quotation Sent</em>. Each toggle activates that event globally.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Customize Templates</h4><p>Go to <strong>Settings → SMS Templates</strong>. Edit the message text for each event. Use variables like <code>{customer_name}</code>, <code>{amount}</code>, <code>{reference}</code> to personalize messages.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Send a Test Message</h4><p>At the bottom of the SMS settings page, enter a phone number and click <strong>Send Test SMS</strong> to verify the gateway is working.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>View Delivery Logs</h4><p>Go to <strong>Reports → SMS Log</strong> to see all sent messages with gateway, status (sent/failed), and error details.</p></div></div>
          <div class="tip">💡 WhatsApp requires a WhatsApp Business API account. The phone number must be verified in your gateway dashboard.</div>
        </div>
      </div>
      <div x-show="lang==='fr'">
        <div class="doc-card">
          <h3>Notifications SMS & WhatsApp</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>Choisir une passerelle</h4><p>Allez dans <strong>Paramètres → SMS / WhatsApp</strong>. Sélectionnez votre passerelle : <strong>Twilio</strong>, <strong>Nexmo</strong>, <strong>Infobip</strong>, <strong>Termii</strong> ou <strong>WhatsApp</strong>. Saisissez les identifiants de la passerelle choisie.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>Activer les déclencheurs d'événements</h4><p>Activez les événements qui envoient un SMS : <em>Vente créée</em>, <em>Paiement reçu</em>, <em>Facture en retard</em>, <em>Devis envoyé</em>.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>Personnaliser les modèles</h4><p>Allez dans <strong>Paramètres → Modèles SMS</strong>. Modifiez le texte pour chaque événement. Utilisez des variables comme <code>{customer_name}</code>, <code>{amount}</code>.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>Envoyer un SMS test</h4><p>En bas de la page SMS, saisissez un numéro et cliquez sur <strong>Envoyer un SMS test</strong>.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>Journal de livraison</h4><p>Allez dans <strong>Rapports → Journal SMS</strong> pour voir tous les messages avec statut et détails d'erreur.</p></div></div>
          <div class="tip">💡 WhatsApp nécessite un compte API WhatsApp Business avec un numéro vérifié.</div>
        </div>
      </div>
      <div x-show="lang==='ar'">
        <div class="doc-card">
          <h3>إشعارات SMS وواتساب</h3>
          <div class="step"><div class="step-num">1</div><div class="step-body"><h4>اختيار بوابة الإرسال</h4><p>اذهب إلى <strong>الإعدادات ← SMS / واتساب</strong>. اختر بوابتك: <strong>Twilio</strong> أو <strong>Nexmo</strong> أو <strong>Infobip</strong> أو <strong>Termii</strong> أو <strong>واتساب</strong>. أدخل بيانات الاعتماد للبوابة المختارة.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><h4>تفعيل مشغلات الأحداث</h4><p>فعّل الأحداث التي ترسل رسالة SMS: <em>إنشاء بيع</em>، <em>استلام دفعة</em>، <em>فاتورة متأخرة</em>، <em>إرسال عرض سعر</em>.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><h4>تخصيص القوالب</h4><p>اذهب إلى <strong>الإعدادات ← قوالب الرسائل</strong>. عدّل نص الرسالة لكل حدث. استخدم متغيرات مثل <code>{customer_name}</code> و<code>{amount}</code>.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><h4>إرسال رسالة تجريبية</h4><p>في أسفل صفحة الرسائل، أدخل رقم هاتف وانقر على <strong>إرسال رسالة تجريبية</strong>.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><h4>سجل التسليم</h4><p>اذهب إلى <strong>التقارير ← سجل الرسائل</strong> لعرض جميع الرسائل المرسلة مع الحالة وتفاصيل الخطأ.</p></div></div>
          <div class="tip">💡 يتطلب واتساب حساب WhatsApp Business API مع رقم هاتف مُفعَّل.</div>
        </div>
      </div>
    </div>

  </div>{{-- end content --}}
</div>{{-- end flex --}}
</div>
@endsection
