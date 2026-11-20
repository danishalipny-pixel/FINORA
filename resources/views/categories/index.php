<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Financial Categories</h2>
        <p class="text-secondary small mb-0">Organize and label your cashflow with custom and standard financial categories.</p>
    </div>
    <div>
        <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-plus-lg"></i> Add Custom Category
        </button>
    </div>
</div>

<!-- Navigation Tabs for Income vs Expense -->
<ul class="nav nav-pills mb-4" id="categoryTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="expense-tab" data-bs-toggle="pill" data-bs-target="#expense-pane" type="button" role="tab">
            <i class="bi bi-arrow-up-right-circle me-1"></i> Expense Categories (<?= count($expenseCategories) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="income-tab" data-bs-toggle="pill" data-bs-target="#income-pane" type="button" role="tab">
            <i class="bi bi-arrow-down-left-circle me-1"></i> Income Categories (<?= count($incomeCategories) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="categoryTabContent">
    <!-- Expense Pane -->
    <div class="tab-pane fade show active" id="expense-pane" role="tabpanel">
        <div class="row g-3">
            <?php foreach ($expenseCategories as $cat): ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="fin-card h-100 mb-0 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="kpi-icon-badge" style="background-color: <?= e($cat['color']) ?>20; color: <?= e($cat['color']) ?>;">
                                        <i class="bi <?= e($cat['icon']) ?>"></i>
                                    </div>
                                    <div>
                                        <h3 class="h6 fw-bold mb-0 text-truncate" style="max-width: 140px;"><?= e($cat['name']) ?></h3>
                                        <?php if ($cat['is_default']): ?>
                                            <span class="badge bg-secondary-subtle text-secondary small">System Default</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary small">Custom</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!$cat['is_default']): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-1">
                                            <li>
                                                <button class="dropdown-item small rounded" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $cat['id'] ?>">
                                                    <i class="bi bi-pencil me-2"></i> Edit
                                                </button>
                                            </li>
                                            <li>
                                                <form method="POST" action="/categories/<?= $cat['id'] ?>/delete" class="m-0 form-delete-confirm">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item small rounded text-danger">
                                                        <i class="bi bi-trash me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center small text-secondary">
                            <span>Usage:</span>
                            <span class="fw-semibold text-primary"><?= $cat['transaction_count'] ?> transaction<?= $cat['transaction_count'] !== 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!$cat['is_default']): ?>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCategoryModal<?= $cat['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="/categories/<?= $cat['id'] ?>/edit">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="expense">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" value="<?= e($cat['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Icon Class (Bootstrap Icons)</label>
                                            <input type="text" class="form-control" name="icon" value="<?= e($cat['icon']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Color Code</label>
                                            <input type="color" class="form-control form-control-color w-100" name="color" value="<?= e($cat['color']) ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-fin-primary btn-sm">Update Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Income Pane -->
    <div class="tab-pane fade" id="income-pane" role="tabpanel">
        <div class="row g-3">
            <?php foreach ($incomeCategories as $cat): ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="fin-card h-100 mb-0 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="kpi-icon-badge" style="background-color: <?= e($cat['color']) ?>20; color: <?= e($cat['color']) ?>;">
                                        <i class="bi <?= e($cat['icon']) ?>"></i>
                                    </div>
                                    <div>
                                        <h3 class="h6 fw-bold mb-0 text-truncate" style="max-width: 140px;"><?= e($cat['name']) ?></h3>
                                        <?php if ($cat['is_default']): ?>
                                            <span class="badge bg-secondary-subtle text-secondary small">System Default</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary small">Custom</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!$cat['is_default']): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-1">
                                            <li>
                                                <button class="dropdown-item small rounded" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $cat['id'] ?>">
                                                    <i class="bi bi-pencil me-2"></i> Edit
                                                </button>
                                            </li>
                                            <li>
                                                <form method="POST" action="/categories/<?= $cat['id'] ?>/delete" class="m-0 form-delete-confirm">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item small rounded text-danger">
                                                        <i class="bi bi-trash me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center small text-secondary">
                            <span>Usage:</span>
                            <span class="fw-semibold text-success"><?= $cat['transaction_count'] ?> transaction<?= $cat['transaction_count'] !== 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/categories">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Custom Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Freelance Consulting, Gym & Fitness" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="expense">Expense Category</option>
                            <option value="income">Income Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class (Bootstrap Icons)</label>
                        <select class="form-select" name="icon">
                            <option value="bi-tag">bi-tag (Default Tag)</option>
                            <option value="bi-briefcase">bi-briefcase (Business / Work)</option>
                            <option value="bi-heart-pulse">bi-heart-pulse (Health / Fitness)</option>
                            <option value="bi-car-front">bi-car-front (Automotive / Travel)</option>
                            <option value="bi-mortarboard">bi-mortarboard (Education / Courses)</option>
                            <option value="bi-controller">bi-controller (Gaming / Entertainment)</option>
                            <option value="bi-airplane">bi-airplane (Flights / Vacations)</option>
                            <option value="bi-camera">bi-camera (Media / Creative)</option>
                            <option value="bi-cup-hot">bi-cup-hot (Coffee / Dining)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Accent</label>
                        <input type="color" class="form-control form-control-color w-100" name="color" value="#3B82F6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fin-primary btn-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
