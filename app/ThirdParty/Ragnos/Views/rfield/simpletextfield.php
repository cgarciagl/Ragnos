<div class="divfield col-sm-4 mb-1">
    <div class='form-control' id='group_<?= $name ?>'>
        <label class="control-label">
            <?= $label; ?> :
        </label>
        <?php
        $data = [
            'name'        => $name,
            'label'       => $label,
            'value'       => $value,
            'placeholder' => $placeholder,
        ];
        ?>
        <?= view('App\ThirdParty\Ragnos\Views\rfield/inputfield', $data); ?>
    </div>
</div>