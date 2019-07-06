<?php
assert(isset($errors) && is_array($errors));
assert(isset($profileId) && is_string($profileId));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($fullName) && is_string($fullName));
assert(isset($barcode) && is_string($barcode));
assert(isset($translationHelp) && is_array($translationHelp));

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
                <?php }

                echo Former::hidden('id')->value($profileId);

                echo Former::horizontal_open()
                ->id('MyForm')
                ->rules(['name' => 'required'])
                ->method('POST');

                echo Former::xlarge_text('name')
                ->class('form-control')
                ->label($translationTitles['Name'])
                ->value($fullName)
                ->readonly()
                ->required();

                echo Former::xlarge_text('barcode')
                ->class('form-control')
                ->label($translationTitles['Username'])
                ->value($barcode)
                ->readonly()
                ->required();

                echo Former::password('password')
                ->class('form-control')
                ->label($translationTitles['Password'])
                ->required()
                ->help($translationHelp['Password']);

                echo Former::password('password_confirmation')
                ->class('form-control')
                ->label($translationTitles['PasswordConfirmation'])
                ->required();

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

