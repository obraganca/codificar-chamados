<?php $__env->startSection('title', 'Novo chamado #' . $chamado->id); ?>

<?php $__env->startSection('content'); ?>

<h2>
    Novo chamado aberto
</h2>

<p>
    Olá, <?php echo e($usuario->name); ?>!
</p>

<p>
    Um novo chamado foi aberto no sistema.
</p>

<?php echo $__env->make(
    'emails.components.chamados.info',
    ['chamado' => $chamado]
, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make(
    'emails.components.chamados.button',
    [
        'url' => url('/chamados/' . $chamado->id),
        'text' => 'Visualizar chamado'
    ]
, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('emails.layouts.ticket', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/emails/chamados/created.blade.php ENDPATH**/ ?>