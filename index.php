<?php
session_start();
// ライブラリの読み込み
require_once 'mpdf60/mpdf.php';
$max_page = $_GET["num"];

// mPDFクラス作成
$mpdf = new mPDF( "ja","A4-L");

for($i = 1; $i <= $max_page; $i++){
    //一時htmlファイル名の指定
    $filename = "tempfiles/".$i.".html";

    //一時htmlファイルの作成（空のファイル）
    touch($filename);

    //セッションにて現在の作成してる枚数カウントをチェック
    //（外部phpファイルを読み込む際に引数を与えられないためセッションを使用（include））
    $_SESSION["pdf_page"] = $i; //現在の枚数カウント
    $contents = ""; //初期化

    //出力バッファリングを開始（実行された template.php の文字列そのもの（＝htmlとしての出力文字列）が欲しい）
    ob_start();  
    //出力バッファに外部ファイルを読み込む  
    include('./template.php');  
    //出力バッファの内容を変数に入れる  
    $contents = ob_get_contents();  
    //出力バッファリングを終了  
    ob_end_clean();
    // 現在、$contentsには $i枚目のhtml文字列が入っている

    //htmlファイル作成
    file_put_contents($filename, $contents);
    //ここで $i枚目 のhtmlファイルそのものができる

    // HTMLとCSSを読み込み（上記のものを材料にしてPDF化をする（mpdfの機能））
    //DOMとしての入れ物の準備
    $html_doc = new DOMDocument();

    //先ほど作ったhtmlの文字列を取得
    $html_doc->loadHTMLFile($filename);

    //このCSSを元にPDFを構築する
    $css = file_get_contents('./materials/style.css');

    // ファイル名を指定（ページ名をセット）
    $mpdf->setTitle( "MyPDF-".$i.".pdf");

    // HTMLとCSSをPDFへ書き込む
    $mpdf->WriteHTML( $css, 1); //先ほど指定したCSSを用いる設定
    $mpdf->WriteHTML( $html_doc->saveHTML(), 2); //先ほど指定したhtmlファイルをPDF化する

    //最終ページでない限り、ページを追加する（連結）
    if($i != $max_page){
        $mpdf->AddPage();
    }
}

    // 出力
    //$mpdf->Output($i.".pdf","I"); //この行はhtmlとして出力
    $mpdf->Output("temppdf/temp.pdf","F");

    //ここでサーバにファイルが保存されるので、あとはそのファイルへのリンクを表示させ、DLさせる
    echo '<a href="temppdf/temp.pdf">作成されたPDF</a>';

return;

