<?php

class Crawler
{
    public function main()
    {
        $fp = fopen('php://output', 'w');
        $results = array();
        for ($i = 1; $i <= 656; $i ++) {
            foreach ($this->getData($i) as $result) {
                fputcsv($fp, array(
                    $result->name,
                    $result->account_name,
                    $result->bank_name,
                    $result->account,
                    $result->address,
                    $result->allow,
                    $result->date,
                ));
            }
        }
        echo json_encode($results);
    }

    public function getData($page)
    {
        $params = array(
            'xdUrl' => '/wSite/PoliticAccount/PAQuery.jsp',
            'doQuery' => '1',
            'accountType' => '擬參選人',
            'accountName' => '',
            'keyword' => '',
            'lv2' => '',
            'lv4' => '',
            'lv3' => '',
            'electionId' => '',
            'buttonType' => '',
            'politicYear' => '',
            'orderFlag' => '',
            'page' => $page,
        );
        $terms = array();
        foreach ($params as $key => $value) {
            $terms[] = urlencode($key) . '=' . urlencode($value);
        }
        $url = 'http://sunshine.cy.gov.tw/GipOpenWeb/wSite/sp';
        $url .= '?' . implode('&', $terms);
        $curl = curl_init($url);
        error_log($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $terms));

        $ret = curl_exec($curl);
        //$ret = file_get_contents('tmp');

        $doc = new DOMDocument;
        @$doc->loadHTML($ret);

        $table_doms = $doc->getElementsByTagName('table');
        foreach ($table_doms as $table_dom) {
            if ($table_dom->getAttribute('summary') == '資料表格') {
                break;
            }
        }

        $results = array();
        foreach ($table_dom->getElementsByTagName('tr') as $tr_dom) {
            $td_doms = $tr_dom->getElementsByTagName('td');
            $td_dom = $td_doms->item(0);
            if (!$td_dom) {
                continue;
            }
            $result = new StdClass;
            $result->name = $td_doms->item(0)->nodeValue;
            $result->account_name = $td_doms->item(1)->nodeValue;
            $result->bank_name = $td_doms->item(2)->nodeValue;
            $result->account = $td_doms->item(3)->nodeValue;
            $result->address = $td_doms->item(4)->nodeValue;
            $result->allow = $td_doms->item(5)->nodeValue;
            $result->date = $td_doms->item(6)->nodeValue;

            $results[] = $result;
        }

        return $results;
        
    }
}

$c = new Crawler;
$c->main();
