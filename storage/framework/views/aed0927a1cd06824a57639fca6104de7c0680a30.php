<?php
    /** @var \Itstructure\GridView\Columns\BaseColumn[] $columnObjects */
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    /** @var boolean $useFilters */
    $checkboxesExist = false;
?>
<style>
    .table-bordered tfoot tr td {
        border-width: 0;
    }
</style>
<div class="card">
    <div class="card-header">
        <?php if($title): ?>
            <h2 class="card-title"><?php echo $title; ?></h2>
        <?php endif; ?>
        <div class="float-right">
            <?php if($paginator->onFirstPage()): ?>
                <?php echo trans('grid_view::grid.page-info', [
                    'start' => '<b>1</b>',
                    'end' => '<b>' . $paginator->perPage() . '</b>',
                    'total' => '<b>' . $paginator->total() . '</b>',
                ]); ?>

            <?php elseif($paginator->currentPage() == $paginator->lastPage()): ?>
                <?php echo trans('grid_view::grid.page-info', [
                    'start' => '<b>' . (($paginator->currentPage() - 1) * $paginator->perPage() + 1) . '</b>',
                    'end' => '<b>' . $paginator->total() . '</b>',
                    'total' => '<b>' . $paginator->total() . '</b>',
                ]); ?>

            <?php else: ?>
                <?php echo trans('grid_view::grid.page-info', [
                    'start' => '<b>' . (($paginator->currentPage() - 1) * $paginator->perPage() + 1) . '</b>',
                    'end' => '<b>' . (($paginator->currentPage()) * $paginator->perPage()) . '</b>',
                    'total' => '<b>' . $paginator->total() . '</b>',
                ]); ?>

            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <table class="table <?php if($tableBordered): ?> table-bordered <?php endif; ?> <?php if($tableStriped): ?> table-striped <?php endif; ?> <?php if($tableHover): ?> table-hover <?php endif; ?> <?php if($tableSmall): ?> table-sm <?php endif; ?>">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <?php $__currentLoopData = $columnObjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th <?php echo $column_obj->buildHtmlAttributes(); ?>>

                            <?php if($column_obj->getSort() === false || $column_obj instanceof \Itstructure\GridView\Columns\ActionColumn): ?>
                                <?php echo e($column_obj->getLabel()); ?>


                            <?php elseif($column_obj instanceof \Itstructure\GridView\Columns\CheckboxColumn): ?>
                                <?php ($checkboxesExist = true); ?>
                                <?php if($useFilters): ?>
                                    <?php echo e($column_obj->getLabel()); ?>

                                <?php else: ?>
                                    <input type="checkbox" id="grid_view_checkbox_main" class="form-control form-control-sm" <?php if($paginator->count() == 0): ?> disabled="disabled" <?php endif; ?> />
                                <?php endif; ?>

                            <?php else: ?>
                                <a href="<?php echo e(\Itstructure\GridView\Helpers\SortHelper::getSortableLink(request(), $column_obj)); ?>"><?php echo e($column_obj->getLabel()); ?></a>
                            <?php endif; ?>

                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
                <?php if($useFilters): ?>
                    <tr>
                        <form action="" method="get" id="grid_view_filters_form">
                            <td></td>
                            <?php $__currentLoopData = $columnObjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td>
                                    <?php if($column_obj instanceof \Itstructure\GridView\Columns\CheckboxColumn): ?>
                                        <input type="checkbox" id="grid_view_checkbox_main" class="form-control form-control-sm" <?php if($paginator->count() == 0): ?> disabled="disabled" <?php endif; ?> />
                                    <?php else: ?>
                                        <?php echo $column_obj->getFilter()->render(); ?>

                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <input type="submit" class="d-none">
                        </form>
                    </tr>
                <?php endif; ?>
            </thead>

            <form action="<?php echo e($rowsFormAction); ?>" method="post" id="grid_view_rows_form">
                <tbody>
                    <?php $__currentLoopData = $paginator->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e(($paginator->currentPage() - 1) * $paginator->perPage() + $key + 1); ?></td>
                            <?php $__currentLoopData = $columnObjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td><?php echo $column_obj->render($row); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="<?php echo e(count($columnObjects) + 1); ?>">
                            <div class="mx-1">
                                <div class="row">
                                    <div class="col-12 col-xl-8 text-center text-xl-left">
                                        <?php echo e($paginator->render('grid_view::pagination')); ?>

                                    </div>
                                    <div class="col-12 col-xl-4 text-center text-xl-right">
                                        <?php if($useFilters): ?>
                                            <button id="grid_view_search_button" type="button" class="btn btn-primary"><?php echo e($searchButtonLabel); ?></button>
                                            <button id="grid_view_reset_button" type="button" class="btn btn-warning"><?php echo e($resetButtonLabel); ?></button>
                                        <?php endif; ?>
                                        <?php if(($checkboxesExist || $useSendButtonAnyway) && $paginator->count() > 0): ?>
                                            <button type="submit" class="btn btn-danger"><?php echo e($sendButtonLabel); ?></button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <input type="hidden" value="<?php echo csrf_token(); ?>" name="_token">
            </form>
        </table>
    </div>
</div>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        $('#grid_view_checkbox_main').click(function (event) {
            $('input[role="grid-view-checkbox-item"]').prop('checked', event.target.checked);
        });

        $('#grid_view_search_button').click(function () {
            $('#grid_view_filters_form').submit();
        });

        $('#grid_view_reset_button').click(function () {
            $('input[role="grid-view-filter-item"]').val('');
            $('select[role="grid-view-filter-item"]').prop('selectedIndex', 0);
        });
    });
</script><?php /**PATH /var/www/www-root/data/www/vk.ipostx.ru/vendor/itstructure/laravel-grid-view/src/../resources/views/grid.blade.php ENDPATH**/ ?>