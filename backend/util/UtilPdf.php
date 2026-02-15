<?php

/**
 * PDFユーティリティ
 *
 * @author kanemiya
 *
 */
class UtilPdf {



	/**
	 * PDFファイルを出力する
	 * @param string $type 出力種別（I:ブラウザ, D:ダウロード, F:ファイル設置, S:文字表示（？））
	 * @param string $body_tpl 本文テンプレートファイル名 ※拡張子は不要
	 * @param array $params 表示パラメータ
	 * @return boolean 結果(true:成功, false:失敗)
	 * 
	 * EX) UtilPdf::output('I', 'regist_qr', null, $params);
	 */
	public static function output($type = 'I', $body_tpl, $file_name = 'pdf', $params = null) {

		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8',false, false);
		$pdf->addPage();
		$pdf->setFont('kozgopromedium', 'B', 10);

		$smarty = new Smarty();
		$smarty->compile_dir = TMP_DIR;

		if(count($params)) {

			foreach ($params as $column => $param) {
				$smarty->assign($column, $param);
			}

		}

		$tpl_name = VIEW_DIR . '/pdf/'. $body_tpl . '.html';
		if (!file_exists($tpl_name))
			return false;

		$html = $smarty->fetch($tpl_name);

		$pdf->writeHTML($html);
		$pdf->Output($file_name . '.pdf', $type);

		return true;

	}


}