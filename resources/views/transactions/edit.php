<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">Edit Transaction</h2>
                <p class="text-secondary small mb-0">Modify details for transaction #<?= $transaction['id'] ?>.</p>
            </div>
            <a href="/transactions" class="btn btn-fin-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Transactions
            </a>
        </div>

        <div class="fin-card">
            <div class="fin-card-body">
                <form method="POST" action="/transactions/<?= $transaction['id'] ?>/edit" id="transactionForm">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- Transaction Type -->
                        <div class="col-12 col-md-6">
                            <label for="tx_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('type') ? 'is-invalid' : '' ?>" id="tx_type" name="type" required>
                                <option value="expense" <?= old('type', $transaction['type']) === 'expense' ? 'selected' : '' ?>>Expense (Money Out)</option>
                                <option value="income" <?= old('type', $transaction['type']) === 'income' ? 'selected' : '' ?>>Income (Money In)</option>
                                <option value="transfer" <?= old('type', $transaction['type']) === 'transfer' ? 'selected' : '' ?>>Account Transfer</option>
                            </select>
                            <?php if (has_error('type')): ?>
                                <div class="invalid-feedback"><?= e(error('type')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Amount (PKR) -->
                        <div class="col-12 col-md-6">
                            <label for="amount" class="form-label">Amount (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" min="0.01" class="form-control <?= has_error('amount') ? 'is-invalid' : '' ?>" id="amount" name="amount" value="<?= e(old('amount', $transaction['amount'])) ?>" required>
                            </div>
                            <?php if (has_error('amount')): ?>
                                <div class="invalid-feedback d-block"><?= e(error('amount')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Source Account -->
                        <div class="col-12 col-md-6">
                            <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('account_id') ? 'is-invalid' : '' ?>" id="account_id" name="account_id" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>" <?= old('account_id', $transaction['account_id']) == $acc['id'] ? 'selected' : '' ?>>
                                        <?= e($acc['name']) ?> (Balance: <?= currency($acc['current_balance']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (has_error('account_id')): ?>
                                <div class="invalid-feedback"><?= e(error('account_id')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Destination Account -->
                        <div class="col-12 col-md-6" id="tx_dest_account_container" style="<?= $transaction['type'] === 'transfer' ? '' : 'display: none;' ?>">
                            <label for="destination_account_id" class="form-label">Destination Account <span class="text-danger">*</span></label>
                            <select class="form-select <?= has_error('destination_account_id') ? 'is-invalid' : '' ?>" id="destination_account_id" name="destination_account_id">
                                <option value="">Select Destination Account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>" <?= old('destination_account_id', $transaction['destination_account_id']) == $acc['id'] ? 'selected' : '' ?>>
                                        <?= e($acc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (has_error('destination_account_id')): ?>
                                <div class="invalid-feedback"><?= e(error('destination_account_id')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-6" id="tx_category_container" style="<?= $transaction['type'] === 'transfer' ? 'display: none;' : '' ?>">
                            <label for="tx_category" class="form-label">Category</label>
                            <select class="form-select <?= has_error('category_id') ? 'is-invalid' : '' ?>" id="tx_category" name="category_id">
                                <option value="">Select Category</option>
                                <optgroup label="Expense Categories">
                                    <?php foreach ($expenseCategories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" data-type="expense" <?= old('category_id', $transaction['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Income Categories">
                                    <?php foreach ($incomeCategories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" data-type="income" <?= old('category_id', $transaction['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <?php if (has_error('category_id')): ?>
                                <div class="invalid-feedback"><?= e(error('category_id')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Transaction Date -->
                        <div class="col-12 col-md-6">
                            <label for="transaction_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?= has_error('transaction_date') ? 'is-invalid' : '' ?>" id="transaction_date" name="transaction_date" value="<?= e(old('transaction_date', $transaction['transaction_date'])) ?>" required>
                            <?php if (has_error('transaction_date')): ?>
                                <div class="invalid-feedback"><?= e(error('transaction_date')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-12 col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <?php foreach (['Cash', 'Bank Transfer', 'Debit Card', 'Credit Card', 'Online', 'Cheque', 'Other'] as $m): ?>
                                    <option value="<?= $m ?>" <?= old('payment_method', $transaction['payment_method']) === $m ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label">Description / Payee <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" id="description" name="description" value="<?= e(old('description', $transaction['description'])) ?>" required>
                            <?php if (has_error('description')): ?>
                                <div class="invalid-feedback"><?= e(error('description')) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Reference ID (Optional) -->
                        <div class="col-12 col-md-6">
                            <label for="reference_id" class="form-label">Reference / Cheque # <span class="text-secondary small">(Optional)</span></label>
                            <input type="text" class="form-control" id="reference_id" name="reference_id" value="<?= e(old('reference_id', $transaction['reference_id'])) ?>">
                        </div>

                        <!-- Notes (Optional) -->
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes <span class="text-secondary small">(Optional)</span></label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?= e(old('notes', $transaction['notes'])) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <form method="POST" action="/transactions/<?= $transaction['id'] ?>/delete" class="form-delete-confirm m-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-fin-outline-danger">
                                <i class="bi bi-trash"></i> Delete Transaction
                            </button>
                        </form>
                        <div class="d-flex gap-2">
                            <a href="/transactions" class="btn btn-fin-secondary">Cancel</a>
                            <button type="submit" class="btn btn-fin-primary">
                                <i class="bi bi-check2-circle"></i> Update Transaction
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
