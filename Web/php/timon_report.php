<?php
$tcpdf_include_dirs = array(
	realpath('/var/www/UI/report/tcpdf.php'),
	'/usr/share/php/tcpdf/tcpdf.php',
	'/usr/share/tcpdf/tcpdf.php',
	'/usr/share/php-tcpdf/tcpdf.php',
	'/var/www/tcpdf/tcpdf.php',
	'/var/www/html/tcpdf/tcpdf.php',
	'/usr/local/apache2/htdocs/tcpdf/tcpdf.php'
);
foreach ($tcpdf_include_dirs as $tcpdf_include_path) {
	if (@file_exists($tcpdf_include_path)) {
		require_once($tcpdf_include_path);
		break;
	}
}

include_once '/var/www/UI/report/timon_pdf.php';
include_once '/var/www/UI/report/timon_graph.php';
include_once '/var/www/UI/report/report_common_function.php';
include_once "/var/www/UI/report/get_sys_info.php";
include_once '/var/www/UI/report/get_data_traffic_line.php';
include_once '/var/www/UI/report/get_data_top10.php';
include_once '/var/www/UI/report/get_data_resource_line.php';
include_once '/var/www/UI/report/get_data_protocol_statistics.php';
include_once '/var/www/UI/report/get_data_port_statistics.php';
include_once '/var/www/UI/report/get_data_detected_attack_statistics.php';
include_once '/var/www/UI/report/get_data_detected_port_statistics.php';
include_once "/var/www/UI/report/report_data_ips.php";

$result = file_get_contents("/var/www/UI/report/report_param.conf");
$arg_arr = json_decode($result, true);

$arg = $arg_arr;

$start_time = $arg['start_year'] . "-" . $arg['start_month'] . "-" . $arg['start_day'];
$end_time = $arg['end_year'] . "-" . $arg['end_month'] . "-" . $arg['end_day'];
$period_text = $start_time . " ~ " . $end_time;

if($arg['lang'] == "English") {
    include_once '/var/www/UI/report/lang/eng.php';
}
else {
    include_once '/var/www/UI/report/lang/kor.php';
}

$file_name = "Report_". exec('hostname') . "_". RevisionNumber() ."_" . date("Ymd_H_i_s") . "_(" . $start_time . "~". $end_time . ")_Instant.pdf";

$isRunningReport = fopen("/var/www/UI/report/isRunningReport.txt", "w");
fwrite($isRunningReport, json_encode($file_name));
fclose($isRunningReport);

ob_end_clean(); //    the buffer and never prints or returns anything.
ob_start(); // it starts buffering

// create new PDF document
$pdf = new TIMONPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tester');
$pdf->SetTitle('Timon Report');
$pdf->SetSubject('');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set font
$pdf->SetFont('malgungothic', '', 15);

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

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$bMargin = $pdf->getBreakMargin();
// get current auto-page-break mode
$auto_page_break = $pdf->getAutoPageBreak();
// disable auto-page-break
$pdf->SetAutoPageBreak(false, 0);

$pdf->Image('/var/www/UI/report/images/cover.jpg', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
// EOD는 앞에 빈칸을 두면 안됨.
$pdf->Ln(20);
$pdf->SetFont('malgungothic', 'B', 40);
$txt = <<<EOD
Timon $label_report
EOD;
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(20);

$pdf->SetFont('malgungothic', '', 15);
$txt = <<<EOD
$label_duration : $period_text
EOD;
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(160);

$pdf->SetFont('malgungothic', '', 20);
$txt = <<<EOD
$label_company
EOD;
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

$pdf->SetFont('malgungothic', '', 15);

// restore auto-page-break status
$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
// set the starting point for the page content
$pdf->setPageMark();
$pdf->setPrintHeader(true);

$index_of_bookmark = 1;
$page_of_bookmark = 2;
$foreach_count = 0;

foreach($arg['ippool'] as $key => $value) {
    if($foreach_count != 0) {
        $page = $pdf->getPage();
        $page_of_bookmark = $page + 1;
    }

    $start_time_for_data = $arg['start_year'] . "-" . $arg['start_month'] . "-" . $arg['start_day'] . " 00:00:00";
    $end_time_for_data = $arg['end_year'] . "-" . $arg['end_month'] . "-" . $arg['end_day'] . " 23:59:59";

    $object_name = "";
    if($key == "total") {
        $object_name = "default";
    }
    else {
        $object_name = $value;
    }

    $report_data = Report_Data_Get($start_time_for_data, $end_time_for_data, "60", $object_name);
    $report_name = "";

    if($key == "total") {
        $report_name = $label_total . " " . $label_report;
    }
    else {
        $report_name = $label_object . "(" . $value . ")" . $label_report;;
    }

    $index_of_bookmark_second = 1;
    $printFooter = false;

    $label_arr = array(
        'statistics' => $label_statistics,
        'traffic' => $label_traffic,
        'division' => $label_division,
        'detect' => $label_detect,
        'occur' => $label_occur,
        'packets' => $label_packet_count,
        'bytes' => $label_byte_count
    );

    $traffic_table_data = make_all_traffic_table_data($report_data, $label_arr);
    
    $pdf->Bookmark($index_of_bookmark . '. '. $report_name, 0, 0, $page_of_bookmark, 'B', array(0,64,128));
    if($arg["summary_infomation"] == "Y") {
        // 요약 정보 시작 --------------------------------------------------------------------------------
        $pdf->SetPageHeader($report_name, $label_summary_information, $label_duration . ': ' . $period_text);
        $pdf->AddPage();
        
        if($printFooter == false) {
            $pdf->setPrintFooter(true);
        }
        
        $pdf->Bookmark($index_of_bookmark . '.' . $index_of_bookmark_second . ' ' . $label_summary_information, 1, 0, '', '', array(128,0,0));
        $index_of_bookmark_second++;
        $pdf->textGradient('▣ ' . $label_operational_information);
        $pdf->Ln(-3);
    
        $label_arr = array(
            'items' => $label_items,
            'used_rates' => $label_used_rates,
            'host_name' => $label_host_name,
            'version' => $label_version,
            'ha_mode' => $label_ha_mode,
            'information' => $label_information
        );
    
        $sys_info_table_data = get_sys_data($label_arr);
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell("", $sys_info_table_data['title'], $sys_info_table_data['width_arr'], $sys_info_table_data['data'], true, "left");
        $pdf->writeHTMLCell(101, '', 14, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(10);
        // 운영 정보 종료 --------------------------------------------------------------------------------
        // 트래픽 현황 시작 --------------------------------------------------------------------------------
        $pdf->textGradient('▣ ' . $label_traffic_status);
        $pdf->Ln(-3);
        $label_arr = array(
            'total' => $label_total
        );
        $traffic_status_data = set_traffic_status_all_text($report_data, $label_arr);
        $pdf->textUnderLine($label_traffic_status_message, $traffic_status_data, "0");
    
        $label_arr = array(
            'statistics' => $label_statistics,
            'traffic' => $label_traffic,
            'division' => $label_division,
            'detect' => $label_detect,
            'occur' => $label_occur,
            'packets' => $label_packet_count,
            'bytes' => $label_byte_count
        );

        $table_data = $pdf->SetDataInSpanTitleArray($traffic_table_data['title'], $traffic_table_data['data']);
        $pdf->SpanWithLeftColTable($table_data);
    
        // make_traffic_stack_graph($report_data);
        // if(file_exists('/var/www/UI/report/images/background_stack_graph.png')) {
        //     unlink('/var/www/UI/report/images/background_resource.png');
        // }
    
        // copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_resource.png');
        // $y = $pdf->getY();
        // $mask = $pdf->Image('/var/www/UI/report/images/background_resource.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
        // $pdf->Image("/var/www/UI/report/images/line_graph_resource_.png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
        // $pdf->Ln(45);
    
        // 트래픽 현황 종료 --------------------------------------------------------------------------------
        // 공격 탐지 현황 시작 --------------------------------------------------------------------------------
        $pdf->textGradient('▣ ' . $label_attack_detection_status);
        $pdf->Ln(-3);
        $attack_detection_status_data = get_detect_total_count_text($report_data);
        $pdf->textUnderLine($label_attack_count_message, $attack_detection_status_data);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'category' => $label_category,
            'attack_name' => $label_attack_name,
            'packets' => $label_packet_count,
            'attack_count' => $label_attack_count
        );
    
        $detected_attack_types_data = make_detect_attack_status_table_data($report_data, $label_arr);
    
        $y = $pdf->getY();
        $cell_text = $pdf->makeHTMLMultiCell($label_major_attack_types, $detected_attack_types_data['title'], $detected_attack_types_data['width_arr'], $detected_attack_types_data['data']);
        $pdf->writeHTMLCell(101, '', 14, $y + 2, $cell_text, 0, 0, 0, true, 'J', true);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'category' => $label_category,
            'attacker_ip' => $label_attacker_ip,
            'packets' => $label_packet_count,
            'attack_count' => $label_attack_count
        );
    
        $attacker_ip_top10_data = make_attacker_ip_table_data($report_data, $label_arr);
    
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell($label_major_attacker_ip, $attacker_ip_top10_data['title'], $attacker_ip_top10_data['width_arr'], $attacker_ip_top10_data['data']);
        $pdf->writeHTMLCell(101, '', 115, $y, $right_cell_text, 0, 1, 0, true, 'J', true);
        // 공격 탐지 현황 종료 --------------------------------------------------------------------------------
    }
    if($arg["system_status"] == "Y") {
        // 시스템 자원 시작 --------------------------------------------------------------------------------
        $pdf->SetPageHeader($report_name, $label_system_resource_status . " " . $label_statistics, $label_duration . ': ' . $period_text);
        $pdf->AddPage();

        if($printFooter == false) {
            $pdf->setPrintFooter(true);
        }

        $pdf->Bookmark($index_of_bookmark . '.' . $index_of_bookmark_second . ' ' . $label_system_resource_status . " " . $label_statistics, 1, 0, '', '', array(128,0,0));
        $index_of_bookmark_second++;
        $pdf->textGradient('▣ ' . $label_system_resource_status . " " . $label_statistics);
        $pdf->Ln(-3);
        $pdf->textUnderLine($label_system_resource_status_message, null);
    
        $label_arr = array(
            'items' => $label_items,
            'used_rates' => $label_used_rates
        );
        $resource_table_data = make_resource_table_data($report_data, $label_arr);
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell("", $resource_table_data['title'], $resource_table_data['width_arr'], $resource_table_data['data'], true);
        $pdf->writeHTMLCell(101, '', 14, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(10);
    
        $pdf->textGradient('▣ ' . $label_system_resource_status . " " . $label_trend);
        $pdf->Ln(-3);
    
        if(file_exists('/var/www/UI/report/images/background_resource.png')) {
            unlink('/var/www/UI/report/images/background_resource.png');
        }

        make_resource_graph($report_data);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_resource.png');
        $y = $pdf->getY();
        $mask = $pdf->Image('/var/www/UI/report/images/background_resource.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
        $pdf->Image("/var/www/UI/report/images/line_graph_resource_.png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
        $pdf->Ln(45);
        // 시스템 자원 종료 --------------------------------------------------------------------------------
    }
    if($arg["traffic_statistics"] == "Y") {
        // TRAFFIC 시작 ---------------------------------------------------------
        $pdf->SetPageHeader($report_name, $label_traffic_statistics, $label_duration . ': ' . $period_text);
        $pdf->AddPage();

        if($printFooter == false) {
            $pdf->setPrintFooter(true);
        }

        $pdf->Bookmark($index_of_bookmark . '.' . $index_of_bookmark_second . ' ' . $label_traffic_statistics, 1, 0, '', '', array(128,0,0));
        $index_of_bookmark_second++;
    
        $label_arr = array(
            'statistics' => $label_statistics,
            'traffic' => $label_traffic,
            'division' => $label_division,
            'detect' => $label_detect,
            'occur' => $label_occur,
            'packets' => $label_packet_count,
            'bytes' => $label_byte_count,
            'traffic_statistics' => $label_traffic_statistics,
            'total' => $label_total
        );
    
        $pdf->textGradient('▣ ' . $label_total_traffic);
        $pdf->Ln(-3);
        $traffic_status_data = set_traffic_status_text($report_data, 'total', $label_arr);
        $pdf->textUnderLine($label_traffic_status_message, $traffic_status_data, "0");
        $pdf->Ln(-3);
        $traffic_total_data = make_traffic_table_data($traffic_table_data, 'total', $label_arr);
        $table_data = $pdf->SetDataInSpanTitleArray($traffic_total_data['title'], $traffic_total_data['data']);
        $pdf->SpanWithLeftColTable($table_data);
        $pdf->textUnderLine($label_traffic_status_sub_message, array(0 => $label_total), "0");
        $pdf->Ln(-3);
    
        $type_arr = array(
        'inbound_graph_' . $object_name . '_bps'
        , 'outbound_graph_' . $object_name . '_bps'
        , 'inbound_graph_' . $object_name . '_pps'
        , 'outbound_graph_' . $object_name . '_pps');

        make_traffic_graph($report_data, 'total', $object_name);

        for($i=0;$i<4;$i++) {
            if(file_exists('/var/www/UI/report/images/background_total' . $i . $object_name . '.png')) {
                unlink('/var/www/UI/report/images/background_total' . $i . $object_name . '.png');
            }
    
            copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_total'. $i . $object_name . '.png');
            $y = $pdf->getY();
            $mask = $pdf->Image('/var/www/UI/report/images/background_total' . $i . $object_name .'.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
            $pdf->Image("/var/www/UI/report/images/line_graph_total_" . $type_arr[$i] . ".png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
            $pdf->Ln(45);
        }
    
        $pdf->AddPage();
        $pdf->textGradient('▣ TCP ' . $label_traffic);
        $pdf->Ln(-3);
    
        $traffic_status_data = set_traffic_status_text($report_data, 'tcp', $label_arr);
        $pdf->textUnderLine($label_traffic_status_message, $traffic_status_data, "0");
        $pdf->Ln(-3);
        $traffic_tcp_data = make_traffic_table_data($traffic_table_data, 'tcp', $label_arr);
        $table_data = $pdf->SetDataInSpanTitleArray($traffic_tcp_data['title'], $traffic_tcp_data['data']);
        $pdf->SpanWithLeftColTable($table_data);
        $pdf->textUnderLine($label_traffic_status_sub_message, array(0 => "TCP"), "0");
        $pdf->Ln(-3);
    
        $type_arr = array(
        'inbound_graph_' . $object_name . '_bps'
        , 'outbound_graph_' . $object_name . '_bps'
        , 'inbound_graph_' . $object_name . '_pps'
        , 'outbound_graph_' . $object_name . '_pps');

        make_traffic_graph($report_data, 'tcp', $object_name);

        for($i=0;$i<4;$i++) {
            if(file_exists('/var/www/UI/report/images/background_tcp' . $i . $object_name . '.png')) {
                unlink('/var/www/UI/report/images/background_tcp' .$i . $object_name . '.png');
            }

            copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_tcp' . $i . $object_name . '.png');
            $y = $pdf->getY();
            $mask = $pdf->Image('/var/www/UI/report/images/background_tcp' . $i . $object_name . '.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
            $pdf->Image("/var/www/UI/report/images/line_graph_tcp_" . $type_arr[$i] . ".png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
            $pdf->Ln(45);
        }
    
        $pdf->AddPage();
        $pdf->textGradient('▣ UDP ' . $label_traffic);
        $pdf->Ln(-3);
    
        $traffic_status_data = set_traffic_status_text($report_data, 'udp', $label_arr);
        $pdf->textUnderLine($label_traffic_status_message, $traffic_status_data, "0");
        $pdf->Ln(-3);
        $traffic_total_data = make_traffic_table_data($traffic_table_data, 'udp', $label_arr);
        $table_data = $pdf->SetDataInSpanTitleArray($traffic_total_data['title'], $traffic_total_data['data']);
        $pdf->SpanWithLeftColTable($table_data);
        $pdf->textUnderLine($label_traffic_status_sub_message, array(0 => "UDP"), "0");
        $pdf->Ln(-3);
    
        $type_arr = array(
        'inbound_graph_' . $object_name . '_bps'
        , 'outbound_graph_' . $object_name . '_bps'
        , 'inbound_graph_' . $object_name . '_pps'
        , 'outbound_graph_' . $object_name . '_pps');

        make_traffic_graph($report_data, 'udp', $object_name);

        for($i=0;$i<4;$i++) {
            if(file_exists('/var/www/UI/report/images/background_udp' . $i . $object_name . '.png')) {
                unlink('/var/www/UI/report/images/background_udp' . $i . $object_name . '.png');
            }

            copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_udp' .$i . $object_name . '.png');
            $y = $pdf->getY();
            $mask = $pdf->Image('/var/www/UI/report/images/background_udp' . $i . $object_name .'.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
            $pdf->Image("/var/www/UI/report/images/line_graph_udp_" . $type_arr[$i] . ".png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
            $pdf->Ln(45);
        }
    
        $pdf->AddPage();
        $pdf->textGradient('▣ ICMP ' . $label_traffic);
        $pdf->Ln(-3);
    
        $traffic_status_data = set_traffic_status_text($report_data, 'icmp', $label_arr);
        $pdf->textUnderLine($label_traffic_status_message, $traffic_status_data, "0");
        $pdf->Ln(-3);
        $traffic_total_data = make_traffic_table_data($traffic_table_data, 'icmp', $label_arr);
        $table_data = $pdf->SetDataInSpanTitleArray($traffic_total_data['title'], $traffic_total_data['data']);
        $pdf->SpanWithLeftColTable($table_data);
        $pdf->textUnderLine($label_traffic_status_sub_message, array(0 => "ICMP"), "0");
        $pdf->Ln(-3);
    
        $type_arr = array(
        'inbound_graph_' . $object_name . '_bps'
        , 'outbound_graph_' . $object_name . '_bps'
        , 'inbound_graph_' . $object_name . '_pps'
        , 'outbound_graph_' . $object_name . '_pps');

        make_traffic_graph($report_data, 'icmp', $object_name);

        for($i=0;$i<4;$i++) {
            if(file_exists('/var/www/UI/report/images/background_icmp' . $i . $object_name .'.png')) {
                unlink('/var/www/UI/report/images/background_icmp' . $i . $object_name .'.png');
            }

            copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_icmp' . $i . $object_name .'.png');
            $y = $pdf->getY();
            $mask = $pdf->Image('/var/www/UI/report/images/background_icmp' . $i . $object_name .'.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
            $pdf->Image("/var/www/UI/report/images/line_graph_icmp_" . $type_arr[$i] . ".png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
            $pdf->Ln(45);
        }
        // TRAFFIC 종료 --------------------------------------------------------------------------------
        // Service TRAFFIC 시작 --------------------------------------------------------------------------------
        $pdf->textGradient('▣ ' . $label_service_traffic);
        $pdf->Ln(-6);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'service_name' => $label_service_name,
            'byte_count' => $label_byte_count,
            'packet_count' => $label_packet_count,
            'progress' => $label_progress
        );
    
        $service_traffic_data = get_top10_data($report_data, 'service', $label_arr);
        
        if(file_exists('/var/www/UI/report/images/background_service_traffic_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_service_traffic_' . $object_name . '.png');
        }

        makePieChart('service_traffic_' . $object_name, $service_traffic_data['data']);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_service_traffic_' . $object_name . '.png');
    
        $y = $pdf->getY();
        $mask_bytes = $pdf->Image('/var/www/UI/report/images/background_service_traffic_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', true);
        $pdf->Image('/var/www/UI/report/images/pie_chart_service_traffic_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', false, $mask_bytes);
    
        $service_traffic_data['data'] = make_traffic_service_top10_table_data($service_traffic_data['data'], 'detect');
    
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell("", $service_traffic_data['title'], $service_traffic_data['width_arr'], $service_traffic_data['data']);
        $pdf->writeHTMLCell(101, '', 105, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(3);
        $pdf->textUnderLine($label_traffic_service_message, null);
        $pdf->Ln(-3);
    
        $type_arr = array($object_name . '_pps', $object_name . '_bps');

        make_traffic_service_graph($report_data, $label_arr, $object_name);

        for($i=0;$i<2;$i++) {
            if(file_exists('/var/www/UI/report/images/background_traffic_service'.$i.$object_name.'.png')) {
                unlink('/var/www/UI/report/images/background_traffic_service'.$i.$object_name.'.png');
            }

            copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_traffic_service'.$i.$object_name.'.png');
            $y = $pdf->getY();
            $mask = $pdf->Image('/var/www/UI/report/images/background_traffic_service'.$i.$object_name.'.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
            $pdf->Image("/var/www/UI/report/images/line_graph_traffic_service_" . $type_arr[$i] . ".png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
            $pdf->Ln(45);
        }
        // Service TRAFFIC 종료 --------------------------------------------------------------------------------
    }
    if($arg["detect_attack_statistics"] == "Y") {
        // 공격 종류별 현황 시작 --------------------------------------------------------------------------------
        $pdf->SetPageHeader($report_name, $label_detect_attack_statistics, $label_duration . ': ' . $period_text);
        $pdf->AddPage();

        if($printFooter == false) {
            $pdf->setPrintFooter(true);
        }
        
        $pdf->Bookmark($index_of_bookmark . '.' . $index_of_bookmark_second . ' ' . $label_detect_attack_statistics, 1, 0, '', '', array(128,0,0));
        $index_of_bookmark_second++;
        $pdf->textGradient('▣ ' . $label_attack_types_status);
        $pdf->Ln(-3);
        $attack_types_status_data = get_detect_total_count_text($report_data);
        $pdf->textUnderLine($label_attack_types_status_count_message, $attack_types_status_data);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'category' => $label_category,
            'attack_name' => $label_attack_name,
            'packets' => $label_packet_count,
            'bytes' => $label_byte_count,
            'attack_count' => $label_attack_count,
            'attack_byte' => $label_attack_byte,
            'trend' => $label_trend
        );
    
        $attack_types_status_data = get_top10_data($report_data, 'attack_types_status', $label_arr);
    
        $y = $pdf->getY();
        $cell_text = $pdf->makeHTMLMultiCell("", $attack_types_status_data['title'], $attack_types_status_data['width_arr'], $attack_types_status_data['data']);
        $pdf->writeHTMLCell(101, '', 14, $y + 2, $cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(3);
        $pdf->textUnderLine($label_attack_types_status_message, null);
        $pdf->Ln(-3);
    
        $type_arr = array($object_name . '_Attacks', $object_name . '_bps');

        make_attack_types_status_graph($report_data, $label_arr, $object_name);

        for($i=0;$i<2;$i++) {
            if(file_exists('/var/www/UI/report/images/background_attack_types_status'.$i.$object_name.'.png')) {
                unlink('/var/www/UI/report/images/background_attack_types_status'.$i.$object_name.'.png');
            }

            copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attack_types_status'.$i.$object_name.'.png');
            $y = $pdf->getY();
            $mask = $pdf->Image('/var/www/UI/report/images/background_attack_types_status'.$i.$object_name.'.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
            $pdf->Image("/var/www/UI/report/images/line_graph_attack_types_status_" . $type_arr[$i] . ".png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
            $pdf->Ln(45);
        }
        // 공격 종류별 현황 종료 --------------------------------------------------------------------------------
        // 공격자별 현황 시작 --------------------------------------------------------------------------------
        $pdf->AddPage();
        $pdf->textGradient('▣ ' . $label_attacker_status);
        $pdf->Ln(-3);
        $pdf->textUnderLine($label_attacker_status_message, null);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'attack_name' => $label_attack_name,
            'packets' => $label_packet_count,
            'attack_count' => $label_attack_count,
            'trend' => $label_trend,
            'ip_address' => $label_ip_address,
            'ratio' => $label_ratio
        );
    
        $attacker_status_data = get_top10_data($report_data, 'attacker_status', $label_arr);
        
        if(file_exists('/var/www/UI/report/images/background_attacker_status_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_attacker_status_' . $object_name . '.png');
        }

        makePieChart('attacker_status_' . $object_name, $attacker_status_data['data']);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attacker_status_' . $object_name . '.png');
    
        $y = $pdf->getY();
        $mask_bytes = $pdf->Image('/var/www/UI/report/images/background_attacker_status_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', true);
        $pdf->Image('/var/www/UI/report/images/pie_chart_attacker_status_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', false, $mask_bytes);
    
        $attacker_status_data['data'] = make_attacker_status_top10_table_data($attacker_status_data['data'], 'detect');
    
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell("", $attacker_status_data['title'], $attacker_status_data['width_arr'], $attacker_status_data['data']);
        $pdf->writeHTMLCell(101, '', 105, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(3);
        $pdf->textUnderLine($label_attacker_status_message, null);
        $pdf->Ln(-3);

        if(file_exists('/var/www/UI/report/images/background_attacker_status_1_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_attacker_status_1_' . $object_name . '.png');
        }

        make_attacker_status_graph($report_data, $label_arr, $object_name);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attacker_status_1_' . $object_name . '.png');
        $y = $pdf->getY();
        $mask = $pdf->Image('/var/www/UI/report/images/background_attacker_status_1_' . $object_name . '.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
        $pdf->Image("/var/www/UI/report/images/line_graph_attacker_status_" . $object_name . "_count.png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
        $pdf->Ln(45);
        // 공격자별 현황 종료 --------------------------------------------------------------------------------
        // 공격 대상별 현황 시작 --------------------------------------------------------------------------------
        $pdf->AddPage();
        $pdf->textGradient('▣ ' . $label_attacker_target);
        $pdf->Ln(-3);
        $pdf->textUnderLine($label_attack_target_message, null);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'attack_name' => $label_attack_name,
            'packets' => $label_packet_count,
            'attack_count' => $label_attack_count,
            'trend' => $label_trend,
            'ip_address' => $label_ip_address,
            'ratio' => $label_ratio
        );
    
        $attack_target_data = get_top10_data($report_data, 'attack_target', $label_arr);

        if(file_exists('/var/www/UI/report/images/background_attack_target_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_attack_target_' . $object_name . '.png');
        }

        makePieChart('attack_target_' . $object_name, $attack_target_data['data']);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attack_target_' . $object_name . '.png');
    
        $y = $pdf->getY();
        $mask_bytes = $pdf->Image('/var/www/UI/report/images/background_attack_target_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', true);
        $pdf->Image('/var/www/UI/report/images/pie_chart_attack_target_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', false, $mask_bytes);
    
        $attack_target_data['data'] = make_attack_target_top10_table_data($attack_target_data['data'], 'detect');
    
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell("", $attack_target_data['title'], $attack_target_data['width_arr'], $attack_target_data['data']);
        $pdf->writeHTMLCell(101, '', 105, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(3);
        $pdf->textUnderLine($label_attack_target_sub_message, null);
        $pdf->Ln(-3);

        if(file_exists('/var/www/UI/report/images/background_attack_target_1_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_attack_target_1_' . $object_name . '.png');
        }

        make_attack_target_graph($report_data, $label_arr, $object_name);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attack_target_1_' . $object_name . '.png');
        $y = $pdf->getY();
        $mask = $pdf->Image('/var/www/UI/report/images/background_attack_target_1_' . $object_name . '.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
        $pdf->Image("/var/www/UI/report/images/line_graph_attack_target_" . $object_name . "_count.png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
        $pdf->Ln(45);
        // 공격 대상별 현황 종료 --------------------------------------------------------------------------------
        // 공격 서비스별 현황 시작 --------------------------------------------------------------------------------
        $pdf->AddPage();
        $pdf->textGradient('▣ ' . $label_attack_service);
        $pdf->Ln(-3);
        $pdf->textUnderLine($label_attack_service_message, null);
    
        $label_arr = array(
            'ranking' => $label_ranking,
            'attack_name' => $label_attack_name,
            'packets' => $label_packet_count,
            'attack_count' => $label_attack_count,
            'trend' => $label_trend,
            'service' => $label_service,
            'ratio' => $label_ratio
        );
    
        $attack_service_data = get_top10_data($report_data, 'attack_service', $label_arr);

        if(file_exists('/var/www/UI/report/images/background_attack_service_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_attack_service_' . $object_name . '.png');
        }

        makePieChart('attack_service_' . $object_name, $attack_service_data['data']);

        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attack_service_' . $object_name . '.png');
    
        $y = $pdf->getY();
        $mask_bytes = $pdf->Image('/var/www/UI/report/images/background_attack_service_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', true);
        $pdf->Image('/var/www/UI/report/images/pie_chart_attack_service_' . $object_name . '.png', 14, $y, 91, 66, '', '', '', false, 300, '', false, $mask_bytes);
    
        $attack_service_data['data'] = make_attack_service_top10_table_data($attack_service_data['data'], 'detect');
        
        $y = $pdf->getY();
        $right_cell_text = $pdf->makeHTMLMultiCell("", $attack_service_data['title'], $attack_service_data['width_arr'], $attack_service_data['data']);
        $pdf->writeHTMLCell(101, '', 105, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
        $pdf->Ln(3);
        $pdf->textUnderLine($label_attack_service_sub_message, null);
        $pdf->Ln(-3);

        if(file_exists('/var/www/UI/report/images/background_attack_service_1_' . $object_name . '.png')) {
            unlink('/var/www/UI/report/images/background_attack_service_1_' . $object_name . '.png');
        }

        make_attack_service_graph($report_data, $label_arr, $object_name);
    
        copy('/var/www/UI/report/images/background.png', '/var/www/UI/report/images/background_attack_service_1_' . $object_name . '.png');
        $y = $pdf->getY();
        $mask = $pdf->Image('/var/www/UI/report/images/background_attack_service_1_' . $object_name . '.png', 14, $y, 181.5, 45, '', '', '', false, 1000, '', true);
        $pdf->Image("/var/www/UI/report/images/line_graph_attack_service_" . $object_name . "_count.png", 14, $y, 181.5, 45, '', '', '', false, 1000, '', false, $mask);
        $pdf->Ln(45);
        // 공격 서비스별 현황 종료 --------------------------------------------------------------------------------
    }
    
    if($arg["top10_statistics"] == "Y") {
        $pdf->SetPageHeader($report_name, $label_top10_title, $label_duration . ': ' . $period_text);

        //detect ------------------------------
        $pdf->AddPage();

        if($printFooter == false) {
            $pdf->setPrintFooter(true);
        }
        
        $pdf->Bookmark($index_of_bookmark . '.' . $index_of_bookmark_second . ' ' . $label_top10_title, 1, 0, '', '', array(128,0,0));
        $index_of_bookmark_second++;

        $title_top10_detect_ip_detect =  array($label_ranking, $label_ip_address, $label_attack_count);
        $title_top10_traffic_ip_packet = array($label_ranking, $label_ip_address, $label_packet_count);
        $title_top10_traffic_ip_byte =   array($label_ranking, $label_ip_address, $label_byte_count);
        $title_top10_continent_detect =  array($label_ranking, $label_top10_continent, $label_detect);
        $title_top10_country_detect =    array($label_ranking, $label_top10_country, $label_detect);   
        $title_top10_continent_packet =  array($label_ranking, $label_top10_continent, $label_packets);
        $title_top10_continent_byte =    array($label_ranking, $label_top10_continent, $label_bytes); 
        $title_top10_country_packet =    array($label_ranking, $label_top10_country, $label_packets);
        $title_top10_country_byte =      array($label_ranking, $label_top10_country, $label_bytes);
        $width_top10_list = array(40, 185, 90);
    
        //detect ----------------------------
        $draw_data[] = Array('title'=>$label_top10_attack_outbound, 'msg'=> $label_top10_attack_outbound_msg,
                            'data'=> "top10_attack_outbound", 'add_page'=> false,
                            'data_title' => $title_top10_detect_ip_detect, 'data_width' => $width_top10_list, 'data_type' => 'detect');

        $draw_data[] = Array('title'=>$label_top10_attack_inbound, 'msg'=> $label_top10_attack_inbound_msg,
                            'data'=> "top10_attack_inbound", 'add_page'=> false,
                            'data_title' => $title_top10_detect_ip_detect, 'data_width' => $width_top10_list, 'data_type' => 'detect');

        //detect packets ------------------------------
        $draw_data[] = Array('title'=>$label_top10_attack_packet_outbound, 'msg'=> $label_top10_attack_packet_outbound_msg,
                            'data'=> "top10_attack_packet_outbound", 'add_page'=> true,
                            'data_title' => $title_top10_traffic_ip_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_attack_packet_inbound, 'msg'=> $label_top10_attack_packet_inbound_msg,
                            'data'=> "top10_attack_packet_inbound", 'add_page'=> false,
                            'data_title' => $title_top10_traffic_ip_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        //detect bytes ------------------------------
        $draw_data[] = Array('title'=>$label_top10_attack_byte_outbound, 'msg'=> $label_top10_attack_byte_outbound_msg,
                            'data'=> "top10_attack_byte_outbound", 'add_page'=> true,
                            'data_title' => $title_top10_traffic_ip_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        $draw_data[] = Array('title'=>$label_top10_attack_byte_inbound, 'msg'=> $label_top10_attack_byte_inbound_msg,
                            'data'=> "top10_attack_byte_inbound", 'add_page'=> false,
                            'data_title' => $title_top10_traffic_ip_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        //traffic inbound packet ------------------------------        
        $draw_data[] = Array('title'=>$label_top10_traffic_packet_in_send, 'msg'=> $label_top10_traffic_packet_in_send_msg,
                            'data'=> "top10_traffic_packet_in_send", 'add_page'=> true,
                            'data_title' => $title_top10_traffic_ip_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_traffic_packet_in_recv, 'msg'=> $label_top10_traffic_packet_in_recv_msg,
                            'data'=> "top10_traffic_packet_in_recv", 'add_page'=> false,
                            'data_title' => $title_top10_traffic_ip_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        //traffic outbound packet ------------------------------
        $draw_data[] = Array('title'=>$label_top10_traffic_packet_out_send, 'msg'=> $label_top10_traffic_packet_out_send_msg,
                            'data'=> "top10_traffic_packet_out_send", 'add_page'=> true,
                            'data_title' => $title_top10_traffic_ip_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_traffic_packet_out_recv, 'msg'=> $label_top10_traffic_packet_out_recv_msg,
                            'data'=> "top10_traffic_packet_out_recv", 'add_page'=> false,
                            'data_title' => $title_top10_traffic_ip_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        //traffic inbound bytes ------------------------------
        $draw_data[] = Array('title'=>$label_top10_traffic_byte_in_send, 'msg'=> $label_top10_traffic_byte_in_send_msg,
                            'data'=> "top10_traffic_byte_in_send", 'add_page'=> true,
                            'data_title' => $title_top10_traffic_ip_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        $draw_data[] = Array('title'=>$label_top10_traffic_byte_in_recv, 'msg'=> $label_top10_traffic_byte_in_recv_msg,
                            'data'=> "top10_traffic_byte_in_recv", 'add_page'=> false,
                            'data_title' => $title_top10_traffic_ip_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        //traffic outbound bytes ------------------------------
        $draw_data[] = Array('title'=>$label_top10_traffic_byte_out_send, 'msg'=> $label_top10_traffic_byte_out_send_msg,
                            'data'=> "top10_traffic_byte_out_send", 'add_page'=> true,
                            'data_title' => $title_top10_traffic_ip_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        $draw_data[] = Array('title'=>$label_top10_traffic_byte_out_recv, 'msg'=> $label_top10_traffic_byte_out_recv_msg,
                            'data'=> "top10_traffic_byte_out_recv", 'add_page'=> false,
                            'data_title' => $title_top10_traffic_ip_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        //region continent detect ------------------------------
        $draw_data[] = Array('title'=>$label_top10_attack_continet_out, 'msg'=> $label_top10_attack_continet_out_msg,
                            'data'=> "top10_attack_continet_out", 'add_page'=> true,
                            'data_title' => $title_top10_continent_detect, 'data_width' => $width_top10_list, 'data_type' => 'detect');

        $draw_data[] = Array('title'=>$label_top10_attack_continet_in, 'msg'=> $label_top10_attack_continet_in_msg,
                            'data'=> "top10_attack_continet_in", 'add_page'=> false,
                            'data_title' => $title_top10_continent_detect, 'data_width' => $width_top10_list, 'data_type' => 'detect');

        //region country detect ------------------------------
        $draw_data[] = Array('title'=>$label_top10_attack_country_out, 'msg'=> $label_top10_attack_country_out_msg,
                            'data'=> "top10_attack_country_out", 'add_page'=> true,
                            'data_title' => $title_top10_country_detect, 'data_width' => $width_top10_list, 'data_type' => 'detect');

        $draw_data[] = Array('title'=>$label_top10_attack_country_in, 'msg'=> $label_top10_attack_country_in_msg,
                            'data'=> "top10_attack_country_in", 'add_page'=> false,
                            'data_title' => $title_top10_country_detect, 'data_width' => $width_top10_list, 'data_type' => 'detect');

        //region continent traffic ----------------------------
        $draw_data[] = Array('title'=>$label_top10_traffic_continent_packet_out_recv, 'msg'=> $label_top10_traffic_continent_packet_out_recv_msg,
                            'data'=> "top10_traffic_continent_packet_out_recv", 'add_page'=> true,
                            'data_title' => $title_top10_continent_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_traffic_continent_packet_in_send, 'msg'=> $label_top10_traffic_continent_packet_in_send_msg,
                            'data'=> "top10_traffic_continent_packet_in_send", 'add_page'=> false,
                            'data_title' => $title_top10_continent_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_traffic_continent_byte_out_recv, 'msg'=> $label_top10_traffic_continent_byte_out_recv_msg,
                            'data'=> "top10_traffic_continent_byte_out_recv", 'add_page'=> true,
                            'data_title' => $title_top10_continent_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        $draw_data[] = Array('title'=>$label_top10_traffic_continent_byte_in_send, 'msg'=> $label_top10_traffic_continent_byte_in_send_msg,
                            'data'=> "top10_traffic_continent_byte_in_send", 'add_page'=> false,
                            'data_title' => $title_top10_continent_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        $draw_data[] = Array('title'=>$label_top10_traffic_country_packet_out_recv, 'msg'=> $label_top10_traffic_country_packet_out_recv_msg,
                             'data'=> "top10_traffic_country_packet_out_recv", 'add_page'=> true,
                             'data_title' => $title_top10_country_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_traffic_country_packet_in_send, 'msg'=> $label_top10_traffic_country_packet_in_send_msg,
                             'data'=> "top10_traffic_country_packet_in_send", 'add_page'=> false,
                             'data_title' => $title_top10_country_packet, 'data_width' => $width_top10_list, 'data_type' => 'packet');

        $draw_data[] = Array('title'=>$label_top10_traffic_country_byte_out_recv, 'msg'=> $label_top10_traffic_country_byte_out_recv_msg,
                             'data'=> "top10_traffic_country_byte_out_recv", 'add_page'=> true,
                             'data_title' => $title_top10_country_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        $draw_data[] = Array('title'=>$label_top10_traffic_country_byte_in_send, 'msg'=> $label_top10_traffic_country_byte_in_send_msg,
                             'data'=> "top10_traffic_country_byte_in_send", 'add_page'=> false,
                             'data_title' => $title_top10_country_byte, 'data_width' => $width_top10_list, 'data_type' => 'byte');

        foreach($draw_data as $key => $val)
        {
            $top_data['data'] = get_top10_menu_data($report_data, $val['data']);

            if( $val['add_page'])
                $pdf->AddPage();

            $pdf->textGradient('▣ ' . $val['title']);
            $pdf->Ln(-3); 
            $pdf->textUnderLine($val['msg'], null);            
            Draw_Report_Pichart_List($pdf, $object_name, $val['data'], $top_data['data'], $val['data_title'], $val['data_width'], $val['data_type']);
            $pdf->Ln(25);
        }
    }

    $foreach_count++;
    $index_of_bookmark++;
}

// add a new page for TOC
$pdf->SetPageHeader('', '', '');
$pdf->addTOCPage();

// write the TOC title
$pdf->SetFont('malgungothic', 'B', 16);
$pdf->MultiCell(0, 0, $label_contents, 0, 'C', 0, 1, '', '', true, 0);
$pdf->Ln();

$pdf->SetFont('malgungothic', '', 12);
$pdf->addTOC(2, 'courier', '.', 'INDEX', 'B', array(128,0,0));

// end of TOC page
$pdf->endTOCPage();

//Close and output PDF document
$pdf->Output('/hdd/var/firewall/report/' . $file_name, 'F');
ob_end_flush(); // It's printed here, because ob_end_flush "prints" what's in
              // the buffer, rather than returning it
              //     (unlike the ob_get_* functions)
//============================================================+
// END OF FILE
//============================================================+

if(file_exists("/var/www/UI/report/isRunningReport.txt")) {
    unlink("/var/www/UI/report/isRunningReport.txt");
}

$files = glob('/var/www/UI/report/images/*'); //get all file names
foreach($files as $file_val){
    if(is_file($file_val))
    unlink($file_val); //delete file
}

copy('/var/www/UI/report/images_org/background.png', '/var/www/UI/report/images/background.png');
copy('/var/www/UI/report/images_org/cover.jpg', '/var/www/UI/report/images/cover.jpg');
copy('/var/www/UI/report/images_org/tcpdf_logo.jpg', '/var/www/UI/report/images/tcpdf_logo.jpg');

logAuditData("-", "Report", "Create Report : " . $file_name, "Success");


//data : {'ip', 'value'}
function Top10_list_format($data, $value_type)
{

    foreach($data as $key => $val)
    {        
        if( $val[1] === '')
            continue;

        if( $value_type == "packet")
            $data[$key][1] = number_suff_fix($val[1]);
        else if( $value_type == "byte")
            $data[$key][1] = bytes_suff_fix($val[1]);
        else
            $data[$key][1] = number_format($val[1]);
    }
    return $data;
}

function Draw_Report_Pichart_List($pdf, $object_name, $data_type, $out_data, $title, $width, $value_type)
{           
    $background_img = '/var/www/UI/report/images/background_' . $data_type .'_' . $object_name . '.png';
    $pi_chart_img = '/var/www/UI/report/images/pie_chart_' . $data_type .'_' . $object_name . '.png';

    if(file_exists($background_img)) {
        unlink($background_img);
    }


    makePieChart($data_type . '_' . $object_name, $out_data);

    copy('/var/www/UI/report/images/background.png', $background_img);

    $y = $pdf->getY();
    $mask_bytes = $pdf->Image($background_img, 14, $y, 91, 66, '', '', '', false, 300, '', true);
    $pdf->Image($pi_chart_img, 14, $y, 91, 66, '', '', '', false, 300, '', false, $mask_bytes);

    $out_data = Top10_list_format($out_data, $value_type);
    $y = $pdf->getY();            
    $right_cell_text = $pdf->makeHTMLMultiCell("", $title, $width, $out_data);
    $pdf->writeHTMLCell(101, '', 105, $y + 2, $right_cell_text, 0, 1, 0, true, 'J', true);
}
?>