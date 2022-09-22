<?php

// Include the main TCPDF library (search for installation path).
// require_once('tcpdf_include.php');

error_reporting(-1);
ini_set('display_errors',1);

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config/tcpdf_config_alt.php';

function nl2br2($string) {
	$string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
	return $string;
}

$preview = $_POST['preview'] ?? false;
$debug = $_POST['debug'] ?? false;
$intro = $_POST['intro'] ?? '';
$outro = $_POST['outro'] ?? '';
$vintro = $_POST['vintro'] ?? '';
$voutro = $_POST['voutro'] ?? '';
$desc = $_POST['description'] ?? '';
$valability = $_POST['valability'] ?? '';
$expinfo = $_POST['experiencesdetail'] ?? '';
$supplier = $_POST['supplier'] ?? '';
// $adinfo = $_POST['additional_info'] ?? '';
$xtrainfo = $_POST['extra_info'] ?? '';
$oid = $_POST['oid'] ?? '';

$desc = preg_replace('/^[ \t]*[\r\n]+/m', "<br /> <br />", $desc);
// $desc = nl2br($desc);

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	protected $last_page_flag = false;

	public $oid;
	public $angle = 0;

	public function Close() {
		$this->last_page_flag = true;
		parent::Close();
	}

	function Rotate($angle,$x=-1,$y=-1)
	{
		if($x==-1)
			$x=$this->x;
		if($y==-1)
			$y=$this->y;
		if($this->angle!=0)
			$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0)
		{
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}

	function RotatedText($x, $y, $txt, $angle)
	{
		//Text rotated around its origin
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}

	function _endpage()
	{
		if($this->angle!=0)
		{
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}

	//Page header
    //https://stackoverflow.com/questions/28626655/add-main-header-on-first-page-only-in-tcpdf
	public function Header() {

        if ($this->page > 1) {
            return;
        }

		// Logo
		$image_file = K_PATH_IMAGES.PDF_HEADER_LOGO;
        $this->Image($image_file, PDF_HEADER_X, PDF_HEADER_Y, PDF_HEADER_LOGO_W, PDF_HEADER_LOGO_H, 'PNG', ''
        , 'T', false, 300, '', false, false, 0, false, false, false);
		// Set font
		$this->SetFont('helvetica', '', 10);
		// Title
		if(!empty($this->oid)){
			$this->Cell(0, 15, 'Id '.$this->oid, 0, false, 'R', 0, '', 0, false, 'M', 'M');
		}else{
			$this->Cell(0, 15, 'preview', 0, false, 'R', 0, '', 0, false, 'M', 'M');
		}
		
		if(!$this->oid){
			$this->SetFont('','B',40);
			$this->SetTextColor( 214, 214, 214 );
			$this->RotatedText(10,190,'This is not a real voucher. ',45);
		}
		 
	}

	// Page footer
	public function Footer() {
		return;

		if ($this->last_page_flag) {
			// ... footer for the last page ...

			$this->SetFont('helvetica', '', 9);
			$date = date('d/m/Y');
			
			$this->SetFont('helvetica', '', 12);
			$footerhtml = <<<EOD
<a href="https://complice.ro/">www.complice.ro</a>
<br>
<span style="color:#B99F6F">Give. Experience. Enjoy.</span>
EOD;
			$this->writeHTMLCell(0, 0, PDF_MARGIN_LEFT, -25, $footerhtml, 0, 1, 0, true, '', true);
		}
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->oid = $oid;

// set document information
$pdf->SetCreator(PDF_CREATOR);

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', 'I', 12);

// add a page
$pdf->AddPage();

$vintro = nl2br($vintro);
$voutro = nl2br($voutro);
$expinfo = nl2br($expinfo);
$supplier = nl2br($supplier);
/**
	 * This method allows printing text with line breaks.
	 * They can be automatic (as soon as the text reaches the right border of the cell) or explicit (via the \n character). As many cells as necessary are output, one below the other.<br />
	 * Text can be aligned, centered or justified. The cell block can be framed and the background painted.
	 * @param $w (float) Width of cells. If 0, they extend up to the right margin of the page.
	 * @param $h (float) Cell minimum height. The cell extends automatically if needed.
	 * @param $txt (string) String to print
	 * @param $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
	 * @param $align (string) Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align</li><li>C: center</li><li>R: right align</li><li>J: justification (default value when $ishtml=false)</li></ul>
	 * @param $fill (boolean) Indicates if the cell background must be painted (true) or transparent (false).
	 * @param $ln (int) Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right</li><li>1: to the beginning of the next line [DEFAULT]</li><li>2: below</li></ul>
	 * @param $x (float) x position in user units
	 * @param $y (float) y position in user units
	 * @param $reseth (boolean) if true reset the last cell height (default true).
	 * @param $stretch (int) font stretch mode: <ul><li>0 = disabled</li><li>1 = horizontal scaling only if text is larger than cell width</li><li>2 = forced horizontal scaling to fit cell width</li><li>3 = character spacing only if text is larger than cell width</li><li>4 = forced character spacing to fit cell width</li></ul> General font stretching and scaling values will be preserved when possible.
	 * @param $ishtml (boolean) INTERNAL USE ONLY -- set to true if $txt is HTML content (default = false). Never set this parameter to true, use instead writeHTMLCell() or writeHTML() methods.
	 * @param $autopadding (boolean) if true, uses internal padding and automatically adjust it to account for line width.
	 * @param $maxh (float) maximum height. It should be >= $h and less then remaining space to the bottom of the page, or 0 for disable this feature. This feature works only when $ishtml=false.
	 * @param $valign (string) Vertical alignment of text (requires $maxh = $h > 0). Possible values are:<ul><li>T: TOP</li><li>M: middle</li><li>B: bottom</li></ul>. This feature works only when $ishtml=false and the cell must fit in a single page.
	 * @param $fitcell (boolean) if true attempt to fit all the text within the cell by reducing the font size (do not work in HTML mode). $maxh must be greater than 0 and equal to $h.
	 * @return int Return the number of cells or 1 for html mode.
	 * @public
	 * @since 1.3
	 * @see SetFont(), SetDrawColor(), SetFillColor(), SetTextColor(), SetLineWidth(), Cell(), Write(), SetAutoPageBreak()
	 */
//public function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)
// $border = array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$w = 0; $h = 42; $x = PDF_MARGIN_LEFT; $y = 65;
$ishtml = false; $reseth = true; $stretch = 1; $autopadding = true; $maxh = $h+1;$valign ='B'; $fitcell=true;
if($debug){
	$border = array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
}else{
	$border = 0;
}
$intro = htmlspecialchars_decode($intro);
$pdf->MultiCell($w,$h,$intro,$border,'L', false,1 , $x, $y, $reseth, $stretch, $ishtml,$autopadding,$maxh,$valign,$fitcell);

// Print text using writeHTMLCell()
//writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true)
$pdf->writeHTMLCell(0,0, PDF_MARGIN_LEFT, $h+$y+7, $desc, 0, 1, 0, true, '', true);

$valign ='T'; $fitcell = false;
$outro = htmlspecialchars_decode($outro);
$outro = PHP_EOL.$outro;
$pdf->MultiCell(0, $h,$outro, $border,'L', false,1 , $x, '', $reseth, $stretch, $ishtml,$autopadding,$maxh,$valign,$fitcell);

if(!empty($supplier)){
	$supplier = <<<EOD
	$supplier
EOD;
}

if(!empty($xtrainfo)){
	$xtrainfo = <<<EOD
	Suplimentar: $xtrainfo
EOD;
}
// ADD ADDITIONAL PRODUCT INFO
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);
$adissuedate = 'Data emitere voucher: ' . date('d/m/Y');
$footerhtml = <<<EOD
<br><br><br><br>

<a href="https://complice.ro/">www.complice.ro</a>
<br>
<span style="color:#B99F6F"><strong>Give. Experience. Enjoy.<strong></span>
EOD;
$vfhtml = <<<EOD
$xtrainfo
<br>
<p>Cand esti gata sa te bucuri de experiente memorabile, cu cel putin 2 saptamani inainte de data dorita, 
pentru fiecare experienta in parte, scaneaza QR code-ul de pe acest voucher sau intra pe www.complice.ro si alege “Am un voucher”. 
Un <i>complice</i> se va ocupa de realizarea programarilor, in functie de programul tau si de disponibilitatea partenerilor implicati.</p>
<p>$adissuedate. Valabilitate: $valability</p>
$supplier
<p>In cazul in care exista o alta experienta in portofoliul Complice pe care ti-o doresti mai mult decat 
cele de mai sus, un reprezentant te va ajuta sa schimbi cadoul primit cu experienta dorita, 
in acelasi pret sau achitand o eventuala diferenta.</p>
EOD;
// https://stackoverflow.com/questions/1078729/how-to-calculate-the-height-of-a-multicell-writehtmlcell-in-tcpdf
$pdf->writeHTMLCell(130, 0, PDF_MARGIN_LEFT, '', $vfhtml, 0, 1, 0, true, '', true);

$pdf->SetFont('helvetica', '', 12);
$pdf->writeHTMLCell(130, 0, PDF_MARGIN_LEFT, '', $footerhtml, 0, 1, 0, true, '', true);

$style = array(
	'border' => 2,
	'vpadding' => 'auto',
	'hpadding' => 'auto',
	'fgcolor' => array(0,0,0),
	'bgcolor' => false, //array(255,255,255)
	'module_width' => 1, // width of a single module in points
	'module_height' => 1 // height of a single module in points
);
if($oid){
	$pdf->write2DBarcode('https://complice.ro/programeaza-experienta/?order_number='.$oid, 'QRCODE,H', PDF_HEADER_X+131, PDF_HEADER_Y+10, 42, 42, $style, 'C');
}
else{
	$pdf->write2DBarcode('https://complice.ro/programeaza-experienta/', 'QRCODE,H', PDF_HEADER_X+131, PDF_HEADER_Y+10, 42, 42, $style, 'C');
}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('complice.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
