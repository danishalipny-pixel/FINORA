<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">Import Transactions (CSV)</h2>
                <p class="text-secondary small mb-0">Bulk import your bank statements and expense records seamlessly.</p>
            </div>
            <a href="/transactions" class="btn btn-fin-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Transactions
            </a>
        </div>

        <div class="fin-card mb-4">
            <div class="fin-card-body">
                <form method="POST" action="/transactions/import" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-4 text-center p-4 border border-2 border-dashed rounded-3" style="background-color: var(--fn-light-bg);">
                        <i class="bi bi-file-earmark-arrow-up text-success fs-1 mb-2 d-block"></i>
                        <label for="csv_file" class="form-label fw-bold mb-1">Select CSV File</label>
                        <p class="text-secondary small mb-3">Upload a clean CSV file from your bank or accounting sheet.</p>
                        <div class="d-flex justify-content-center">
                            <input type="file" class="form-control w-auto" id="csv_file" name="csv_file" accept=".csv" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/transactions" class="btn btn-fin-secondary">Cancel</a>
                        <button type="submit" class="btn btn-fin-primary">
                            <i class="bi bi-upload"></i> Process & Import CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CSV Format Instructions Guide -->
        <div class="fin-card">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-info-circle text-primary"></i> Expected CSV Format & Guidelines
                </h3>
            </div>
            <div class="fin-card-body">
                <p class="small text-secondary mb-3">
                    Your CSV file must include headers on the first row. The importer supports the following column names (case-insensitive):
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm small">
                        <thead class="table-light">
                            <tr>
                                <th>Header</th>
                                <th>Required?</th>
                                <th>Description / Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <code><td>Date</td></code>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Transaction date (e.g. <code>2026-11-15</code> or <code>15-11-2026</code>)</td>
                            </tr>
                            <tr>
                                <code><td>Description</td></code>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Payee / Store / Income title (e.g. <code>Monthly Salary</code>, <code>Carrefour Grocery</code>)</td>
                            </tr>
                            <tr>
                                <code><td>Amount</td></code>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Numeric amount greater than zero (e.g. <code>14500.00</code>)</td>
                            </tr>
                            <tr>
                                <code><td>Type</td></code>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td><code>income</code>, <code>expense</code>, or <code>transfer</code> (default: <code>expense</code>)</td>
                            </tr>
                            <tr>
                                <code><td>Category</td></code>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Exact name of category (e.g. <code>Groceries & Food</code>, <code>Housing & Rent</code>)</td>
                            </tr>
                            <tr>
                                <code><td>Account</td></code>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Account name matching one of your active accounts</td>
                            </tr>
                            <tr>
                                <code><td>Payment Method</td></code>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Cash, Bank Transfer, Debit Card, Credit Card, Online (default: Cash)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
