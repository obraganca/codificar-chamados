<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>
        <?php echo $__env->yieldContent('title', 'Sistema de Chamados'); ?>
    </title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            padding: 40px 0;
        }

        .email {
            width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .content {
            padding: 30px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="email">

        <?php echo $__env->make('emails.components.chamados.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="content">
            <?php echo $__env->yieldContent('content'); ?>
        </div>

        <?php echo $__env->make('emails.components.chamados.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>

</div>

</body>
</html><?php /**PATH /var/www/html/resources/views/emails/layouts/ticket.blade.php ENDPATH**/ ?>