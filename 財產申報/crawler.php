<?php

class Crawler
{
    public function main()
    {
        $output = fopen('php://output', 'w');
        $url = 'http://sunshine.cy.gov.tw/GipOpenWeb/wSite/sp?xdUrl=/wSite/SpecialPublication/baseList.jsp&ctNode=';
        for ($i = 1; $i <= 721; $i ++) {
            error_log("$i / 721");
            $params = array();
            if ($i > 1) {
                $params[] = 'nowPage=' . $i;
                $params[] = 'perPage=30';
            }
            $params[] = 'queryStr=+';
            $params[] = 'queryCol=';

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $params));
            $content = curl_exec($curl);
            curl_close($curl);

            $doc = new DOMDocument;
            @$doc->loadHTML($content);

            foreach ($doc->getElementsByTagName('table') as $dom) {
                if ($dom->getAttribute('class') == 'lptb3') {
                    $table_dom = $dom;
                    break;
                }
            }
            $tr_doms = $table_dom->getElementsByTagName('tr');
            for ($j = 1; $j < $tr_doms->length; $j ++) {
                $tr_dom = $tr_doms->item($j);
                $td_doms = $tr_dom->getElementsByTagName('td');

                $ret = array();
                foreach ($td_doms as $td_dom) {
                    $ret[] = preg_replace('#\s#', '', $td_dom->nodeValue);
                }
                fputcsv($output, $ret);
            }
        }

    }
}

$c = new Crawler;
$c->main();
