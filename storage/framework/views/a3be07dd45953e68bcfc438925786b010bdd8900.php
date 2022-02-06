<?php
/**
 * @var \Illuminate\Pagination\LengthAwarePaginator $paginator
 * @var array[] $elements
 */
?>

<?php if($paginator->hasPages()): ?>
    <nav class="d-inline-flex d-xl-flex">
        <ul class="pagination">
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#">&laquo;</a>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e(\Itstructure\GridView\Helpers\UrlSliderHelper::previousPageUrl(request(), $paginator)); ?>">&laquo;</a>
                </li>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active"><span class="page-link"><?php echo e($page); ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="<?php echo e(\Itstructure\GridView\Helpers\UrlSliderHelper::toPageUrl(request(), $paginator, $page)); ?>"><?php echo e($page); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <?php elseif(is_string($element)): ?>
                    <li class="page-item disabled"><span class="page-link"><?php echo e($element); ?></span></li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e(\Itstructure\GridView\Helpers\UrlSliderHelper::nextPageUrl(request(), $paginator)); ?>">&raquo;</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#">&raquo;</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="clearfix"></div>
<?php endif; ?>
<?php /**PATH /var/www/www-root/data/www/vk.ipostx.ru/vendor/itstructure/laravel-grid-view/src/../resources/views/pagination.blade.php ENDPATH**/ ?>