<?php
require_once('../tcpdf/tcpdf.php');
include("../config/db.php");

$id = $_GET['id'];

$pdf = new TCPDF();
$pdf->AddPage();

$res = mysqli_query($conn,"SELECT * FROM bill_items WHERE bill_id=$id");

$html = "<h2>Hospital Bill</h2>";

$total = 0;

while($r = mysqli_fetch_assoc($res)){
    $html .= $r['medicine_id']." Qty ".$r['qty']." Price ".$r['price']."<br>";
    $total += $r['price'];
}

$html .= "<br><b>Total: ₹$total</b>";

$pdf->writeHTML($html);

// ✅ FILE NAME
$filename = "bill_".$id.".pdf";

// ✅ FILE PATH (pdf folder)
$filepath = "../pdf/".$filename;

// ✅ STEP 1: SAVE FILE
$pdf->Output($filepath, 'F');

// ✅ STEP 2: UPDATE DATABASE
mysqli_query($conn,"UPDATE bills SET pdf_file='$filename' WHERE id=$id");

// ✅ STEP 3: DOWNLOAD FILE
$pdf->Output($filename, 'D');

exit;
?>