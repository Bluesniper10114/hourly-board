<?php
assert(isset($errors) && is_array($errors));
assert(isset($translationPlaceholder) && is_array($translationPlaceholder));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($settingsList) && is_array($settingsList));

use Former\Facades\Former;
?>
<script>
$(function(){
    $('form input').each(function(){
        $(this).data({'val':$(this).val()});
    });
    $('form input').change(function(){
        setChangedFlag();
    });
});
function setChangedFlag(){
    var change = false;
    $('form input').each(function(){
        //console.log($(this).data('val') , $(this).val());
        if($(this).data('val') != $(this).val()){
            change = true;
        }
    });
    if(change){
        formDataChanged = true; 
    } else{
        formDataChanged = false;
    }
}
</script>
<div class="content">
    <div class="row">
        <div class="col-md-12">

            <?php
            Former::framework('TwitterBootstrap3');
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

            echo Former::horizontal_open()
                ->id('MyForm')
                ->rules(['name' => 'required'])
                ->method('POST');

            foreach ($settingsList as $index => $setting) {
                echo Former::hidden("data[$index][key]")->value($setting['Key']);

                echo Former::xlarge_text("data[$index][value]")
                    ->class('form-control')
                    ->label($setting['Key'])
                    ->placeholder($translationPlaceholder['Value'])
                    ->value($setting['Value']);

                echo Former::xlarge_text("data[$index][note]")
                    ->class('form-control')
                    ->label($setting['Key'] . " ( " . $translationTitles['Notes'] . " )")
                    ->placeholder($translationPlaceholder['Notes'])
                    ->value($setting['Note']);
            }

            echo Former::actions()
                ->large_primary_submit('Submit')
                ->large_inverse_reset('Reset');

            /**
             * @SuppressWarnings checkAliases
             */
            echo Former::close();
            ?>
        </div>

    </div>
</div>

