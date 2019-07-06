<?php
assert(isset($errors) && is_array($errors));
assert(isset($translationPlaceholders) && is_array($translationPlaceholders));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($filterList) && is_array($filterList));
assert(isset($sortLinks) && is_array($sortLinks));
assert(isset($sortClasses) && is_array($sortClasses));
assert(isset($routingsList) && is_array($routingsList));
?>
<div class="content">
    <div class="row">
        <div class="col-md-12"> 
            <?php

            if (count($errors) > 0) {
                ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php
                        foreach ($errors as $message) {
                            ?>
                            <li><?= $message ?></li>
                            <?php

                        }
                        ?>
                    </ul>
                </div>
                <?php 
            }
            ?>
                <form action="">
                <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="text" class="form-input" name="filterList[ID]" placeholder="<?= $translationPlaceholders['Filter'] ?>" value="<?= $filterList['ID'] ?>" /></th>
                        <th><input type="text" class="form-input" name="filterList[PartNumber]" placeholder="<?= $translationPlaceholders['Filter'] ?>" value="<?= $filterList['PartNumber'] ?>" /></th>
                        <th><input type="text" class="form-input" name="filterList[Description]" placeholder="<?= $translationPlaceholders['Filter'] ?>" value="<?= $filterList['Description'] ?>" /></th>
                        <th><input type="text" class="form-input" name="filterList[Routing]" placeholder="<?= $translationPlaceholders['Filter'] ?>" value="<?= $filterList['Routing'] ?>" /></th>
                    </tr>
                    <tr>
                        <th>
                            <a href="<?= $sortLinks['ID'] ?>" class="sortLink <?= $sortClasses['ID'] ?>">
                                <?= $translationTitles['ID'] ?>
                                <i class="fa fa-sort" ></i>
                                <i class="fa fa-sort-up" ></i>
                                <i class="fa fa-sort-down" ></i>
                            </a>
                        </th>
                        <th>
                            <a href="<?= $sortLinks['PartNumber'] ?>" class="sortLink <?= $sortClasses['PartNumber'] ?>">
                                <?= $translationTitles['PartNumber'] ?>
                                <i class="fa fa-sort" ></i>
                                <i class="fa fa-sort-up" ></i>
                                <i class="fa fa-sort-down" ></i>
                            </a>
                        </th>
                        <th>
                            <a href="<?= $sortLinks['Description'] ?>" class="sortLink <?= $sortClasses['Description'] ?>">
                                <?= $translationTitles['Description'] ?>
                                <i class="fa fa-sort" ></i>
                                <i class="fa fa-sort-up" ></i>
                                <i class="fa fa-sort-down" ></i>
                            </a>
                        </th>
                        <th>
                            <a href="<?= $sortLinks['Routing'] ?>" class="sortLink <?= $sortClasses['Routing'] ?>">
                                <?= $translationTitles['Routing'] ?>
                                <i class="fa fa-sort" ></i>
                                <i class="fa fa-sort-up" ></i>
                                <i class="fa fa-sort-down" ></i>
                            </a>
                        </th>

                    </tr>
                </thead>
                <tbody>
                <?php if (empty($routingsList)) { ?>
                    <tr>
                        <td colspan="4"><?= $translationTitles['EmptyData'] ?></td>
                    </tr>
                    <?php 
                }
                foreach ($routingsList as $row) { ?>
                    <tr>
                        <td><?= $row['ID'] ?></td>
                        <td><?= $row['PartNumber'] ?></td>
                        <td><?= $row['Description'] ?></td>
                        <td><?= $row['Routing'] ?></td>
                    </tr>
                    <?php 
                } ?>
                </tbody>
                </table>
</form>
        </div>

    </div>
</div>

<script>
    $(function(){
        $('input').keydown(function(event) {
            if (event.keyCode == 13) {
                this.form.submit();
                return false;
            }
        });
    });
    </script>