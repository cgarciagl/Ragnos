<table>
    <thead>
        <tr>
            <?php
            $c = (int) (100 / $cuantoscampos);
            foreach ($reportfields as $fieldItem):
                ?>
                <th width='<?= $c ?>%'>
                    <?= "{$modelo->ofieldlist[$fieldItem]->getLabel()}"; ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>