<?php

use app\components\Tools;
use app\components\UrlHelper;
use app\models\db\Amendment;
use app\models\db\Consultation;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var Consultation $consultation
 * @var Amendment $amendment
 */

/** @var \app\controllers\Base $controller */
$controller = $this->context;
$layout     = $controller->layoutParams;

$this->title = 'Änderungsantrag bearbeiten: ' . $amendment->getTitle();
$layout->addBreadcrumb('Administration', UrlHelper::createUrl('admin/index'));
$layout->addBreadcrumb('Anträge', UrlHelper::createUrl('admin/motion/listall'));
$layout->addBreadcrumb('Änderungsantrag');

$layout->addJS('/js/backend.js');
$layout->addCSS('/css/backend.css');
$layout->loadDatepicker();

echo '<h1>' . Html::encode($amendment->getTitle()) . '</h1>';

echo $controller->showErrors();




if ($amendment->status == Amendment::STATUS_SUBMITTED_UNSCREENED) {
    echo Html::beginForm('', 'post', ['class' => 'content', 'id' => 'amendmentScreenForm']);
    $newRev = $amendment->titlePrefix;
    if ($newRev == '') {
        $newRev = $amendment->motion->consultation->getNextAmendmentPrefix($amendment->motionId);
    }

    echo '<input type="hidden" name="titlePrefix" value="' . Html::encode($newRev) . '">';

    echo '<div style="text-align: center;"><button type="submit" class="btn btn-primary" name="screen">';
    echo Html::encode('Freischalten als ' . $newRev);
    echo '</button></div>';

    echo Html::endForm();

    echo "<br>";
}


echo Html::beginForm('', 'post', ['class' => 'content form-horizontal', 'id' => 'amendmentUpdateForm']);


echo '<div class="form-group">';
echo '<label class="col-md-4 control-label" for="amendmentStatus">';
echo 'Status';
echo ':</label><div class="col-md-4">';
$options = ['class' => 'form-control', 'id' => 'amendmentStatus'];
echo Html::dropDownList('amendment[status]', $amendment->status, Amendment::getStati(), $options);
echo '</div><div class="col-md-4">';
$options = ['class' => 'form-control', 'id' => 'amendmentStatusString', 'placeholder' => '...'];
echo Html::textInput('amendment[statusString]', $amendment->statusString, $options);
echo '</div></div>';




echo '<div class="form-group">';
echo '<label class="col-md-4 control-label" for="amendmentTitlePrefix">';
echo 'Antragskürzel';
echo ':</label><div class="col-md-8">';
$options = ['class' => 'form-control', 'id' => 'amendmentTitlePrefix', 'placeholder' => 'z.B. "A1"'];
echo Html::textInput('amendment[titlePrefix]', $amendment->titlePrefix, $options);
echo '<small>z.B. "Ä1", "Ä1neu", "A23-0042" etc. Muss unbedingt gesetzt und eindeutig sein.</small>';
echo '</div></div>';


$locale = Tools::getCurrentDateLocale();

$date = Tools::dateSql2bootstraptime($amendment->dateCreation);
echo '<div class="form-group">';
echo '<label class="col-md-4 control-label" for="amendmentDateCreation">';
echo 'Angelegt am';
echo ':</label><div class="col-md-8"><div class="input-group date" id="amendmentDateCreationHolder">';
echo '<input type="text" class="form-control" name="amendment[dateCreation]" id="amendmentDateCreation"
                value="' . Html::encode($date) . '" data-locale="' . Html::encode($locale) . '">
            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>';
echo '</div></div></div>';

$date = Tools::dateSql2bootstraptime($amendment->dateResolution);
echo '<div class="form-group">';
echo '<label class="col-md-4 control-label" for="amendmentDateResolution">';
echo 'Beschlossen am';
echo ':</label><div class="col-md-8"><div class="input-group date" id="amendmentDateResolutionHolder">';
echo '<input type="text" class="form-control" name="amendment[dateResolution]" id="amendmentDateResolution"
                value="' . Html::encode($date) . '" data-locale="' . Html::encode($locale) . '">
            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>';
echo '</div></div></div>';

$layout->addOnLoadJS(
    '
    var lang = $("html").attr("lang");
    $("#amendmentDateCreationHolder").datetimepicker({
            locale: lang,
    });
    $("#amendmentDateResolutionHolder").datetimepicker({
            locale: lang,
    });
    '
);


echo '<div class="form-group">';
echo '<label class="col-md-4 control-label" for="amendmentNoteInternal">';
echo 'Interne Notiz';
echo ':</label><div class="col-md-8">';
$options = ['class' => 'form-control', 'id' => 'amendmentNoteInternal'];
echo Html::textarea('amendment[noteInternal]', $amendment->noteInternal, $options);
echo '</div></div>';




echo '<div class="saveholder">
<button type="submit" name="save" class="btn btn-primary">Speichern</button>
</div>';

echo Html::endForm();