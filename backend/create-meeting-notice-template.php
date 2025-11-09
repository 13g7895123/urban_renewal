<?php
/**
 * 建立會議通知範本檔案
 * 執行方式：php create-meeting-notice-template.php
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;

echo "開始建立會議通知範本...\n";

// 建立新的 PHPWord 物件
$phpWord = new PhpWord();

// 設定文件預設字型
$phpWord->setDefaultFontName('標楷體');
$phpWord->setDefaultFontSize(12);

// 建立一個新的 section
$section = $phpWord->addSection([
    'marginLeft' => 1440,    // 1 inch = 1440 twips
    'marginRight' => 1440,
    'marginTop' => 1440,
    'marginBottom' => 1440,
]);

// 標題樣式
$titleStyle = [
    'name' => '標楷體',
    'size' => 20,
    'bold' => true,
];

// 一般文字樣式
$normalStyle = [
    'name' => '標楷體',
    'size' => 14,
];

// 小字樣式
$smallStyle = [
    'name' => '標楷體',
    'size' => 12,
];

// 段落置中樣式
$centerParagraph = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];

// 段落左對齊樣式
$leftParagraph = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT];

// 行距樣式
$lineSpacing = [
    'lineHeight' => 1.5,
    'spaceAfter' => 200,
];

// ============================================
// 開始建立範本內容
// ============================================

// 1. 更新會名稱（置中）
$section->addText('${urban_renewal_name}', $titleStyle, array_merge($centerParagraph, ['spaceAfter' => 200]));

// 2. 標題：開會通知單（置中）
$section->addText('開會通知單', ['name' => '標楷體', 'size' => 18, 'bold' => true], array_merge($centerParagraph, ['spaceAfter' => 300]));

// 2.5 受文者：出席人員清單
$section->addText('受文者：', ['name' => '標楷體', 'size' => 14, 'bold' => true], array_merge($leftParagraph, ['spaceAfter' => 100]));
$section->addText('${attendees}', $normalStyle, array_merge($leftParagraph, ['spaceAfter' => 300]));

// 3. 發文字號
$section->addText(
    '發文字號：${notice_doc_number}${notice_word_number}${notice_mid_number}${notice_end_number}',
    $normalStyle,
    array_merge($leftParagraph, ['spaceAfter' => 200])
);

// 4. 空行
$section->addTextBreak(1);

// 5. 主旨
$textRun = $section->addTextRun(array_merge($leftParagraph, ['spaceAfter' => 200]));
$textRun->addText('主旨：', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText('${meeting_type}會議通知', ['name' => '標楷體', 'size' => 14]);

// 6. 空行
$section->addTextBreak(1);

// 7. 說明
$section->addText('說明：', ['name' => '標楷體', 'size' => 14, 'bold' => true], array_merge($leftParagraph, ['spaceAfter' => 150]));

// 8. 會議資訊
$textRun = $section->addTextRun(array_merge($leftParagraph, ['spaceAfter' => 150]));
$textRun->addText('本會訂於 ', $normalStyle);
$textRun->addText('${meeting_date}', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText('（', $normalStyle);
$textRun->addText('${meeting_time}', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText('）假 ', $normalStyle);
$textRun->addText('${meeting_location}', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText(' 召開 ', $normalStyle);
$textRun->addText('${meeting_name}', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText('，敬請準時出席。', $normalStyle);

// 9. 空行
$section->addTextBreak(1);

// 10. 發文說明
$section->addText('${descriptions}', $normalStyle, array_merge($leftParagraph, ['spaceAfter' => 200]));

// 11. 空行
$section->addTextBreak(1);

// 12. 附件
$textRun = $section->addTextRun(array_merge($leftParagraph, ['spaceAfter' => 200]));
$textRun->addText('附件：', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText('${attachments}', $normalStyle);

// 13. 空行
$section->addTextBreak(2);

// 14. 敬啟語
$section->addText('此致', $normalStyle, array_merge($leftParagraph, ['spaceAfter' => 0]));

// 15. 理事長
$textRun = $section->addTextRun(array_merge($leftParagraph, ['spaceAfter' => 300]));
$textRun->addText('${chairman_name}', ['name' => '標楷體', 'size' => 14, 'bold' => true]);
$textRun->addText(' 敬啟', $normalStyle);

// 16. 空行
$section->addTextBreak(2);

// 17. 分隔線
$section->addText(str_repeat('─', 40), $smallStyle, array_merge($leftParagraph, ['spaceAfter' => 200]));

// 18. 聯絡資訊
$section->addText('聯絡資訊', ['name' => '標楷體', 'size' => 13, 'bold' => true], array_merge($leftParagraph, ['spaceAfter' => 150]));

$textRun = $section->addTextRun(array_merge($leftParagraph, ['spaceAfter' => 100]));
$textRun->addText('聯絡人：', $smallStyle);
$textRun->addText('${contact_name}', $smallStyle);

$textRun = $section->addTextRun(array_merge($leftParagraph, ['spaceAfter' => 100]));
$textRun->addText('聯絡電話：', $smallStyle);
$textRun->addText('${contact_phone}', $smallStyle);

// ============================================
// 儲存範本
// ============================================

$outputPath = __DIR__ . '/writable/templates/meeting_notice_template.docx';

// 備份舊檔案
if (file_exists($outputPath)) {
    $backupPath = __DIR__ . '/writable/templates/meeting_notice_template_backup_' . date('YmdHis') . '.docx';
    if (copy($outputPath, $backupPath)) {
        echo "已備份舊範本至: $backupPath\n";
    }
}

// 儲存新範本
try {
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($outputPath);
    echo "✓ 成功建立範本檔案: $outputPath\n";
    echo "\n";
    echo "範本包含以下變數:\n";
    echo "  \${urban_renewal_name}     - 所屬更新會名稱\n";
    echo "  \${attendees}              - 出席人員清單\n";
    echo "  \${notice_doc_number}      - 發文字號前綴\n";
    echo "  \${notice_word_number}     - 字第\n";
    echo "  \${notice_mid_number}      - 發文號碼\n";
    echo "  \${notice_end_number}      - 號後綴\n";
    echo "  \${meeting_type}           - 會議類型\n";
    echo "  \${meeting_date}           - 會議日期\n";
    echo "  \${meeting_time}           - 會議時間\n";
    echo "  \${meeting_location}       - 開會地點\n";
    echo "  \${meeting_name}           - 會議名稱\n";
    echo "  \${descriptions}           - 發文說明\n";
    echo "  \${attachments}            - 附件\n";
    echo "  \${chairman_name}          - 理事長姓名\n";
    echo "  \${contact_name}           - 聯絡人姓名\n";
    echo "  \${contact_phone}          - 聯絡人電話\n";
    echo "\n";
    echo "範本建立完成！\n";
} catch (Exception $e) {
    echo "✗ 建立範本失敗: " . $e->getMessage() . "\n";
    exit(1);
}
