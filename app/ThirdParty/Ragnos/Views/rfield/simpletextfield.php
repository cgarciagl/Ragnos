<div class="divfield col-sm-3 mb-2">
    <div class="form-floating" id='group_<?= $name ?>'>
        <?php
        $data = [
            'name'             => $name,
            'label'            => $label,
            'value'            => $value,
            'placeholder'      => $label,
            'type'             => $type ?? 'text',
            'extra_attributes' => $extra_attributes ?? '',
        ];
        ?>
        <?= view('App\ThirdParty\Ragnos\Views\rfield/inputfield', $data); ?>
        <label for="<?= $name ?>"><?= $label ?></label>
    </div>
</div>