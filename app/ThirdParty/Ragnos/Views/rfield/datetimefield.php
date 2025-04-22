<script src="<?= base_url(); ?>/assets/js/moment-with-locales.js" type="text/javascript"></script>
<script src="<?= base_url(); ?>/assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<link href="<?= base_url(); ?>/assets/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

<div class="divfield col-sm-4 mb-1">
    <div class='input-group shadow-sm' id='group_<?= $name ?>'>
        <label class="control-label">
            <?= $label; ?> :
        </label>

        <div class='input-group date' id='<?= $name ?>datetimepicker'>
            <input name="<?= $name; ?>" type='text' class="form-control" value="<?= $value ?>"
                data-valueant="<?= $value ?>" <?= $extra_attributes; ?> />
            <span class="input-group-text">
                <i class="bi bi-calendar-event"></i>
            </span>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#<?= $name ?>datetimepicker').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:SS',
                locale: 'es'
            }).data("DateTimePicker");
        });
    </script>
</div>