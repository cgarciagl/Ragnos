<div class="panel panel-primary boxshadowround Ragnos-widget">
    <div class="panel-footer">
        <button style="margin-top:15px;" class="btn btn-primary btnbackreport">
            <i class="bi bi-backspace"></i>&nbsp;
            <?= lang('Ragnos.Ragnos_back') ?>
        </button>
        <button style="margin-top:15px;" class="btn btn-primary btnprint">
            <i class="bi bi-printer"></i>
            <?= lang('Ragnos.Ragnos_print') ?>
        </button>
        <button style="margin-top:15px;" class="btn btn-primary exporttoexcel">
            <i class="bi bi-file-earmark-excel"></i>
            <?= lang('Ragnos.Ragnos_excel') ?>
        </button>
    </div>
    <div class="Ragnosreportresult">
        <?= $tabla; ?>
    </div>

    <script type="text/javascript">
        $(function () {
            var b = $('.btnbackreport');
            // if (stackwidgets.count() > 0) {
            //     b.show();
            //     b.click(function (e) {
            //         e.preventDefault();
            //         $(this).parents('.Ragnos-widget').first().remove();
            //         stackwidgets.pop().show('slide');
            //     });
            // } else {
            //     b.hide();
            // }

            $('.Ragnosreportresult table').addClass('table table-condensed');
        });
    </script>

    <script src="<?= base_url(); ?>/assets/js/printThis.min.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {
            $('.btnprint').click(function () {
                $('.Ragnosreportresult').printThis({
                    debug: false,
                    importCSS: true,
                    importStyle: false,
                    printContainer: false,
                    removeInline: true,
                    loadCSS: "<?= base_url(); ?>/assets/css/forprint.min.css",
                    pageTitle: "<?= $title ?> <?= uniqid() ?>"
                });
            });

            $('.exporttoexcel').click(function () {
                // window.open('data:application/vnd.ms-excel,'+$('#imprimible').html());
                var dt = new Date();
                var day = dt.getDate();
                var month = dt.getMonth() + 1;
                var year = dt.getFullYear();
                var hour = dt.getHours();
                var mins = dt.getMinutes();
                var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
                //creating a temporary HTML link element (they support setting file names)
                var a = document.createElement('a');
                //getting data from our div that contains the HTML table
                var data_type = 'data:application/vnd.ms-excel';
                var table_div = $('.Ragnosreportresult').first();
                var table_html = $('.Ragnosreportresult').first().html().replace(/ /g, '%20');
                a.href = data_type + ', ' + table_html;
                //setting the file name
                a.download = 'exportado_' + postfix + '.xls';
                //triggering the function
                a.click();
                //just in case, prevent default behaviour
                e.preventDefault();
            });
        });
    </script>

    <style type="text/css">
        .Ragnosreportresult h1 {
            padding: 5px;
            text-align: center;
            margin-bottom: 5px;
        }

        .Ragnosreportresult h2 {
            padding: 5px;
            text-align: center;
            margin-bottom: 5px;
        }

        .Ragnosreportresult {
            padding: 25px;
        }
    </style>

</div>