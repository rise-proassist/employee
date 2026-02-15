<?php

/**
 * バーコードユーティリティ
 *
 * @author kanemiya
 *
 */

class UtilBarcode {

    /**
     * バーコードを作成する
     *
     * node_idに該当するプログラムに当該クラスのメソッドをコールしてテンプレート側にて、下記のようにコールする
     * <img src="{UtilCommon::get_base_url()}?base_id=Sample&node_id=sample&text={$jan}" alt="barcode" width="160px" />
     *
     * @param string $text バーコードテキスト
     *
     */
    public static function create($text) {

        // フォント
        $font = new BCGFontFile(LIB_DIR . '/barcode/font/Arial.ttf', 18);

        // バーコード化する値
        $bar_text = isset($text) ? $text : 'HELLO';

        // 色定義
        $color_black = new BCGColor(0, 0, 0);
        $color_white = new BCGColor(255, 255, 255);

        $drawException = null;
        try {
            $code = new BCGcode39();
            $code->setScale(2); // Resolution
            $code->setThickness(30); // Thickness
            $code->setForegroundColor($color_black); // Color of bars
            $code->setBackgroundColor($color_white); // Color of spaces
            $code->setFont($font); // Font (or 0)
            $code->parse($bar_text); // Text
            $code->clearLabels();
        } catch(Exception $exception) {
            $drawException = $exception;
        }

        /* Here is the list of the arguments
        1 - Filename (empty : display on screen)
        2 - Background color */
        $drawing = new BCGDrawing('', $color_white);
        if($drawException) {
            $drawing->drawException($drawException);
        } else {
            $drawing->setBarcode($code);
            $drawing->draw();
        }

        // Header that says it is an image (remove it if you save the barcode to a file)
        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename="barcode.png"');

        // Draw (or save) the image into PNG format.
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

    }
}

?>
