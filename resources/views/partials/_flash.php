<?php if (session('status')): ?>
    <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
        <?= e(session('status')) ?>
    </div>
<?php endif; ?>

<?php if (session('success')): ?>
    <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
        <?= e(session('success')) ?>
    </div>
<?php endif; ?>

<?php if (session('error')): ?>
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
        <?= e(session('error')) ?>
    </div>
<?php endif; ?>

<?php if ($errors->any()): ?>
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
        <div class="font-semibold">Please fix the following:</div>
        <ul class="mt-2 list-disc space-y-1 ps-5">
            <?php foreach ($errors->all() as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
