<?php

/***********************************************************************************************************************
 * Author: Ryan Pace Sloan (RPS)
 *
 *
 *
 *
 *
 ***********************************************************************************************************************/
include 'FileUploader.php';
session_start();
session_unset();
session_destroy();
session_start();
if(isset($_FILES)) {
    try{
        $directory = '/var/www/html/KewaPueblo/KewaFiles';
        $newFileName = 'KewaPueblo_GL';
        $fu = new FileUploader($_FILES['file'], $directory, $newFileName);
        //var_dump($fu);

        $fileData = array();
        $handle = fopen($fu->getNewFileName(), "r");
        $headers = explode(",", trim(fgets($handle)));
        $headers[10] = 'Credit';
        //var_dump($headers);
        while(!feof($handle)){
           $fileData[] = explode(",", trim(fgets($handle)));
        }
        fclose($handle);
        //var_dump('FILEDATA', $fileData);

        $data = array();
        $totalDebitSum = $totalCreditSum = 0.00;
        foreach($fileData as $key => $value){
            if(is_array($value) && count($value) === 11) {
                $ee = $value[2];
                $program = $value[6];
                $component = $value[7];
                $data[$ee][$program][$component][] = array('jedate' => $value[0], 'check#' => $value[1], 'ee' => $ee, 'paydate' => $value[3], 'fund' => $value[4], 'glCode' => $value[5], 'program' => $program, 'component' => $component, 'year' => $value[8], 'debit' => (float) $value[9], 'credit' => (float) $value[10], 'lineNumber' => $key);
                $totalDebitSum += $value[9];
                $totalCreditSum += $value[10];
            }
        }
        $totalSumArray = array('debitTotalSum' => $totalDebitSum, 'creditTotalSum' => $totalCreditSum);
        //var_dump('DATA', $totalDebitSum, $totalCreditSum, $data);

        $balance = array();
        foreach($data as $ee => $array){
                //if($ee === '0387-Coriz Stanley'){var_dump($array);}
            foreach($array as $program => $arr){
                //if($ee === '0387-Coriz Stanley'){var_dump($arr);}
                foreach($arr as $component => $a){
                    $debits = $credits = array();
                    $sumDebits = $sumCredits = 0.00;
                    //if($ee === '0387-Coriz Stanley'){var_dump($a);}
                    foreach($a as $key => $value) {
                        //if($ee === '0387-Coriz Stanley'){var_dump($value);}
                        $debits[] = $value['debit'];
                        $credits[] = $value['credit'];
                        if($value['glCode'] === '1010') {
                            $lineNumber = $value['lineNumber'];
                        }
                    }

                    $sumDebits = array_sum($debits);
                    $sumCredits = array_sum($credits);
                    $balance[$ee][$program][$component][] = array($sumDebits, $sumCredits, $lineNumber);
                    //if($ee === '0387-Coriz Stanley'){var_dump('DEBITS', $sumDebits, $debits, 'CREDITS', $sumCredits, $credits);}
                }
            }
        }
        //var_dump('BALANCE', $balance);

        $output = $toBalance = array();
        foreach($balance as $ee => $array) {
            //if($ee === '0004-Pacheco Keith'){var_dump($array);}
            foreach ($array as $program => $arr) {
                //if($ee === '0004-Pacheco Keith'){var_dump($arr);}
                foreach ($arr as $component => $a) {
                    //if($ee === '0004-Pacheco Keith'){var_dump($a);}
                    foreach($a as $key => $value){
                        //if($ee === '0004-Pacheco Keith'){var_dump($value);}
                        $dbt = $cdt = '';
                        $debit = round($value[0], 2);
                        $credit = round($value[1], 2);
                        $lineNumber = $value[2];
                        //var_dump('DEBIT', $debit, 'CREDIT', $credit, $debit === $credit, $debit == $credit);
                        if($debit === $credit){
                            $code = "$ee | $program | $component | Debit Total: $$debit | Credit Total: $$credit | <img src='img/checkmark-30x30.png' height='30' width='30'/>";
                            $output[$ee][$program][$component]['balance'] = $code;
                        }else{
                            if($debit > $credit){
                                //+
                                $difference = number_format(($debit - $credit), 2);
                                $dbt = "<span class='highlight'>Debit Total = $$debit</span>";
                                $cdt = "Credit Total = $$credit";
                                $toBalance[$ee][$program][$component][] = array('ee' => $ee, 'debit' => $debit, 'credit' => $credit, 'difference' => $difference, 'debitTest' => true, 'lineNumber' => $lineNumber );
                            }else if($debit < $credit){
                                //-
                                $difference = number_format(($credit - $debit), 2);
                                $dbt = "Debit Total = $$debit";
                                $cdt = "<span class='highlight'>Credit Total = $$credit</span>";
                                $toBalance[$ee][$program][$component][] = array('ee' => $ee, 'debit' => $debit, 'credit' => $credit, 'difference' => $difference, 'debitTest' => false, 'lineNumber' => $lineNumber);
                            }
                            $code = "$ee | $program | $component | $dbt | $cdt | <span class='red'>$$difference</span>";
                            $output[$ee][$program][$component]['notBalance'] = $code;

                        }
                    }
                }
            }
        }
        //var_dump('TOBALANCE', $toBalance);
        //var_dump('OUTPUT', $output);
        //var_dump(count($toBalance));

        $final = array();
        $lineCreationCount = 0;
        foreach($toBalance as $ee => $array){
            foreach($array as $program => $arr){
                foreach($arr as $component => $a){
                    foreach($a as $key => $value) {
                        //var_dump($a[$key]);
                        //var_dump($data[$ee][$program][$component][$key]);
                        $glCodeInput = '1010';
                        if ($a[$key]['debitTest']) {
                            $newLine = array($data[$ee][$program][$component][$key]['jedate'], $data[$ee][$program][$component][$key]['check#'],
                                $data[$ee][$program][$component][$key]['ee'], $data[$ee][$program][$component][$key]['paydate'],
                                $data[$ee][$program][$component][$key]['fund'], $glCodeInput,
                                $data[$ee][$program][$component][$key]['program'], $data[$ee][$program][$component][$key]['component'],
                                $data[$ee][$program][$component][$key]['year'], '0',
                                $toBalance[$ee][$program][$component][$key]['difference']);
                            $output[$ee][$program][$component][] = '<div class="debit">';
                            $output[$ee][$program][$component][] = "<span><strong>$newLine[2] | $newLine[6] | $newLine[7] | GL Code: $glCodeInput  &rarr; Created Credit Line: +$". $toBalance[$ee][$program][$component][$key]['difference'] . " </strong></span>";
                            $output[$ee][$program][$component][] = "<span><strong>Previous Credit Sum: $" . $toBalance[$ee][$program][$component][$key]['credit']."</strong></span>";
                            $output[$ee][$program][$component][] = "<span><strong>New Credit Sum: <span class='green'>$" . number_format(($toBalance[$ee][$program][$component][$key]['credit'] + $toBalance[$ee][$program][$component][$key]['difference']),2) . "</span></strong></span>";
                            $output[$ee][$program][$component][] = '</div>';
                        } else {
                            //var_dump($value['lineNumber'], $fileData[$value['lineNumber']], $fileData[$value['lineNumber']][10]);
                            $originalCredit = $fileData[$value['lineNumber']][10];
                            $newLine = array($data[$ee][$program][$component][$key]['jedate'], $data[$ee][$program][$component][$key]['check#'],
                                $data[$ee][$program][$component][$key]['ee'], $data[$ee][$program][$component][$key]['paydate'],
                                $data[$ee][$program][$component][$key]['fund'], $glCodeInput,
                                $data[$ee][$program][$component][$key]['program'], $data[$ee][$program][$component][$key]['component'],
                                $data[$ee][$program][$component][$key]['year'], '0',
                                $temp = $originalCredit - $toBalance[$ee][$program][$component][$key]['difference']);
                            $output[$ee][$program][$component][] = '<div class="credit">';
                            $output[$ee][$program][$component][] = "<span><strong>$newLine[2] | $newLine[6] | $newLine[7] | GL Code: $glCodeInput &rarr; Modifying Credit By: -$". $toBalance[$ee][$program][$component][$key]['difference'] ."</strong></span>";
                            $output[$ee][$program][$component][] = "<span><strong>Adjusting Line: " . (intval($value['lineNumber']) + 2) . " | Original Credit Value: $". $originalCredit ." | New Credit Value: $". $temp ."</strong></span>";
                            $output[$ee][$program][$component][] = "<span><strong>Previous Credit Sum: $" . $toBalance[$ee][$program][$component][$key]['credit']."</strong></span>";
                            $output[$ee][$program][$component][] = "<span><strong>New Credit Sum: <span class='green'>$" . number_format(($toBalance[$ee][$program][$component][$key]['credit'] - $toBalance[$ee][$program][$component][$key]['difference']),2). "</span></strong></span>";
                            $output[$ee][$program][$component][] = "</div>";
                            unset($fileData[$value['lineNumber']]);
                        }
                        $final[] = $newLine;
                        $lineCreationCount++;
                    }
                }
            }
        }

        //var_dump('FINAL BEFORE', $final);

        foreach($fileData as $data){
            if(is_array($data) && count($data) === 11){
                $final[] = $data;
            }
        }
        //var_dump(count($fileData), count($final), 'FINAL AFTER', $final);

        $emp = $gl = $pro = $comp = array();
        foreach ($final as $key => $row) {
            $emp[$key]  = $row[2];
            $gl[$key] = $row[5];
            $pro[$key] = $row[6];
            $comp[$key] = $row[7];
        }
        array_multisort($emp, SORT_ASC, $pro, SORT_ASC, $gl, SORT_ASC, $comp, SORT_ASC, $final);

        $finalDebitSum = $finalCreditSum = 0.00;
        foreach($final as $data){
            $finalDebitSum += $data[9];
            $finalCreditSum += $data[10];
        }
        $finalSum = array('finalDebitSum' => $finalDebitSum, 'finalCreditSum' => $finalCreditSum);

        //var_dump('SORTED FINAL', $final);

        $today = new DateTime('now');
        $dateFormat = $today->format("m-d-y-H-i-s");

        $fileName = "/var/www/html/KewaPueblo/processed/KewaPueblo_Processed_GL_File_" .$dateFormat . ".csv";
        $handle = fopen($fileName, 'wb');
        fputcsv($handle,$headers);
        for($i = 0; $i < count($final); $i++){
            fputcsv($handle, $final[$i]);
        }
        fclose($handle);

        $_SESSION['fileName'] = $fileName;
        $_SESSION['totalSum'] = $totalSumArray;
        $_SESSION['data'] = $output;
        $_SESSION['lineCount'] = count($final);
        $_SESSION['finalSum'] = $finalSum;
        $_SESSION['message'] = 'File Balanced Successfully. Ready for Download.';

        header("Location: ../index.php");

    }catch(Exception $e){
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../index.php");
    }

}else{
    $_SESSION['error'] = 'File Not Selected.';
    header("Location: ../index.php");

}
?>