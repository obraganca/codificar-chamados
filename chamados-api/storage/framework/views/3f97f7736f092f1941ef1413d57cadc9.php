<?php $__env->startSection('title', 'Chamado atribuído #' . $chamado->id); ?>

<?php $__env->startSection('content'); ?>

<h2>Chamado atribuído a você</h2>

<p>Olá, <?php echo e($usuario->name); ?>! O chamado abaixo foi atribuído a você.</p>

<?php echo $__env->make('emails.components.chamados.info', ['chamado' => $chamado], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('emails.components.chamados.button', [
    'url' => url('/chamados/' . $chamado->id),
    'text' => 'Visualizar chamado'
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('emails.layouts.ticket', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/emails/chamados/assigned.blade.php ENDPATH**/ ?>