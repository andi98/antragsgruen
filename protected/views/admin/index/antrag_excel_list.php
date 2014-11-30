<?php
/**
 * @var IndexController $this
 * @var array $antraege
 * @var bool $text_begruendung_zusammen
 */

/*
foreach ($antraege as $ant) {
	echo $ant["antrag"]->revision_name . "<br>";
	foreach ($ant["aes"] as $ae) echo "- " . $ae->revision_name . "<br>";
}
*/

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename=antraege.xlsx');
header('Cache-Control: max-age=0');


define('PCLZIP_TEMPORARY_DIR', '/tmp/');
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

$COL_ANTRAGSNR       = "B";
$COL_ANTRAGSTELLERIN = "C";
$COL_ANTRAGSTEXT     = "D";
if ($text_begruendung_zusammen) {
	$COL_KONTAKT   = "E";
	$COL_VERFAHREN = "F";
} else {
	$COL_BEGRUENDUNG = "E";
	$COL_KONTAKT     = "F";
	$COL_VERFAHREN   = "G";
}

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("Antragsgruen.de");
$objPHPExcel->getProperties()->setLastModifiedBy("Antragsgruen.de");
$objPHPExcel->getProperties()->setTitle($this->veranstaltung->name);
$objPHPExcel->getProperties()->setSubject("Anträge");
$objPHPExcel->getProperties()->setDescription($this->veranstaltung->name . " - Anträge");


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSNR . '2', "Antragsübersicht");
$objPHPExcel->getActiveSheet()->getStyle($COL_ANTRAGSNR . "2")->applyFromArray(array(
	"font" => array(
		"bold" => true
	)
));

$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSNR . '3', 'Antragsnr.');
$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSTELLERIN . '3', 'AntragstellerIn');
if ($text_begruendung_zusammen) {
	$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSTEXT . '3', 'Antragstext u. Begründung');
}  else {
	$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSTEXT . '3', 'Antragstext');
	$objPHPExcel->getActiveSheet()->SetCellValue($COL_BEGRUENDUNG . '3', 'Begründung');
}
$objPHPExcel->getActiveSheet()->SetCellValue($COL_KONTAKT . '3', 'Kontakt');
$objPHPExcel->getActiveSheet()->SetCellValue($COL_VERFAHREN . '3', 'Verfahren');
$objPHPExcel->getActiveSheet()->getStyle($COL_ANTRAGSNR . "3:" . $COL_VERFAHREN . "3")->applyFromArray(array(
	"font" => array(
		"bold" => true
	)
));

$styleThinBlackBorderOutline = array(
	'borders' => array(
		'outline' => array(
			'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
			'color' => array('argb' => 'FF000000'),
		),
	),
);
$objPHPExcel->getActiveSheet()->getStyle($COL_ANTRAGSNR . '2:' . $COL_VERFAHREN . '3')->applyFromArray($styleThinBlackBorderOutline);


PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());


$row = 3;
foreach ($antraege as $ant) {
	/**
	 * @var Antrag $antrag
	 * @var Aenderungsantrag[] $aes
	 */
	$antrag = $ant["antrag"];

	$row++;

	$initiatorInnen_namen   = array();
	$initiatorInnen_kontakt = array();
	foreach ($antrag->antragUnterstuetzerInnen as $unt) {
		if ($unt->rolle == IUnterstuetzerInnen::$ROLLE_INITIATORIN) {
			$initiatorInnen_namen[] = $unt->getNameMitBeschlussdatum(false);
			if ($unt->person->email != "") $initiatorInnen_kontakt[] = $unt->person->email;
			if ($unt->person->telefon != "") $initiatorInnen_kontakt[] = $unt->person->telefon;
		}
	}

	$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSNR . $row, $antrag->revision_name);
	$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSTELLERIN . $row, implode(", ", $initiatorInnen_namen));
	$objPHPExcel->getActiveSheet()->SetCellValue($COL_KONTAKT . $row, implode("\n", $initiatorInnen_kontakt));

	$text_antrag   = str_replace(array("[QUOTE]", "[/QUOTE]"), array("\n\n", "\n\n"), $antrag->text);
	$text_antrag   = HtmlBBcodeUtils::removeBBCode($text_antrag);
	$text_antrag   = HtmlBBcodeUtils::text2zeilen(trim($text_antrag), 120, true);
	$zeilen_antrag = array();
	foreach ($text_antrag as $t) {
		$x             = explode("\n", $t);
		$zeilen_antrag = array_merge($zeilen_antrag, $x);
	}

	$text_begruendung   = str_replace(array("[QUOTE]", "[/QUOTE]"), array("\n\n", "\n\n"), $antrag->begruendung);
	$text_begruendung   = HtmlBBcodeUtils::removeBBCode($text_begruendung);
	$text_begruendung   = HtmlBBcodeUtils::text2zeilen(trim($text_begruendung), 120, true);
	$zeilen_begruendung = array();
	foreach ($text_begruendung as $t) {
		$x                  = explode("\n", $t);
		$zeilen_begruendung = array_merge($zeilen_begruendung, $x);
	}

	if ($text_begruendung_zusammen) {
		$zeilen = array_merge(array("Antrag:"), $zeilen_antrag, array("", "", "Begründung:"), $zeilen_begruendung);
		$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSTEXT . $row, trim(implode("\n", $zeilen)));
		$objPHPExcel->getActiveSheet()->getStyle($COL_ANTRAGSTEXT . $row)->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(14 * count($zeilen));
	} else {
		$objPHPExcel->getActiveSheet()->SetCellValue($COL_ANTRAGSTEXT . $row, trim(implode("\n", $zeilen_antrag)));
		$objPHPExcel->getActiveSheet()->getStyle($COL_ANTRAGSTEXT . $row)->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(14 * count($zeilen_antrag));

		$objPHPExcel->getActiveSheet()->SetCellValue($COL_BEGRUENDUNG . $row, trim(implode("\n", $zeilen_begruendung)));
		$objPHPExcel->getActiveSheet()->getStyle($COL_BEGRUENDUNG . $row)->getAlignment()->setWrapText(true);
	}
}

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
$objPHPExcel->getActiveSheet()->getColumnDimension($COL_ANTRAGSNR)->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension($COL_ANTRAGSTELLERIN)->setWidth(24);
$objPHPExcel->getActiveSheet()->getColumnDimension($COL_ANTRAGSTEXT)->setAutoSize(true);
if (!$text_begruendung_zusammen) $objPHPExcel->getActiveSheet()->getColumnDimension($COL_BEGRUENDUNG)->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension($COL_KONTAKT)->setWidth(24);
$objPHPExcel->getActiveSheet()->getColumnDimension($COL_VERFAHREN)->setWidth(13);


$objPHPExcel->getActiveSheet()->setTitle('Anträge');


// Save Excel 2007 file
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save("php://output");