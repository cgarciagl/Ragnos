<script type="text/javascript">
    var todos = [];
    var demas = [];
    $(function () {
        var b = $('#btnback');
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

        $('#btn_pdf_<?= $controllerUniqueID ?>_View_Report').click(function () {
            var y = $('#<?= $controllerUniqueID ?>form_rep');
            y.find('input[name=typeofreport]').val('pdf');
            y.submit();
        });

        $('#btn_xls_<?= $controllerUniqueID ?>_View_Report').click(function () {
            var y = $('#<?= $controllerUniqueID ?>form_rep');
            y.find('input[name=typeofreport]').val('xls');
            y.submit();
        });

        $('#btn_htm_<?= $controllerUniqueID ?>_View_Report').click(function () {
            var forma = $('#<?= $controllerUniqueID ?>form_rep');
            forma.find('input[name=typeofreport]').val('htm');
            var widget = $(this).parents('.Ragnos-widget').first();
            var widget_container = widget.parent();
            widget.hide();
            //stackwidgets.push(widget);
            var p = forma.serialize();
            getValue('<?= $clase ?>/showReport', p,
                function (s) {
                    $(s).appendTo(widget_container).show('slide');
                });
        });

        $('.nivelselect').change(function () {
            var v = $(this).find('option:selected').val();
            var i = $(this).parents('.nivel').index();
            var cont = $(this).find('option:selected').data('controller');
            var filterf = $(this).find('option:selected').data('filter');
            var fg = $(this).parent('.nivel').children('.filtergroup');
            if (v != '') {
                fg.removeClass('hide');
                var b = fg.find('.reportgroupfilter');
                b.RagnosSearch({
                    controller: cont,
                    filter: filterf
                });
                b.val('');
                checklevels($(this));
            } else {
                fg.hide();
                $(this).parents('.Ragnos-widget').find('.nivel:gt(' + i + ')').each(function (index, Element) {
                    $(Element).find('.searchhiddenfield').val('');
                    $(Element).find('.Ragnosffied').val('');
                    $(Element).hide();
                });
            }
        });

        function checklevels(t) {
            todos = t.parents('.Ragnos-widget').find('.nivel');
            todos.find('.nivelselect').find('option').removeClass('hide').prop('disabled', false);
            todos.each(function (i, e) {
                e = $(e);
                var i = e.index();
                var indexselected = e.find('.nivelselect').find('option:selected').index();
                demas = todos.filter(':gt(' + i + ')');
                demas.each(function (index, elem) {
                    $(elem).find('.nivelselect').find('option').eq(indexselected).addClass('hide').prop('disabled', true);
                    if ($(elem).find('.nivelselect').find('option:selected').index() == indexselected) {
                        $(elem).find('option[value=""]').prop('selected', 'selected');
                        $(elem).children('.filtergroup').hide();
                        $(elem).next('.nivel').hide();
                    }
                });
                if (e.find('.nivelselect')
                    .find('option')
                    .not('.hide').length == 1) {
                    e.addClass('hide');
                }
                if (indexselected > 0) {
                    demas.first().removeClass('hide');
                }
            });

        }

        var e1 = $('.nivel').first();
        if (e1.find('.nivelselect').find('option').not('.hide').length == 1) {
            e1.addClass('hide');
        }
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