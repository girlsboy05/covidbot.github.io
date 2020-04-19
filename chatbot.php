<?php

    if(isset($_REQUEST['hub_challenge'])) {
        $challenge = $_REQUEST['hub_challenge'];
        $token = $_REQUEST['hub_verify_token'];
    }
    if($token=="covidbot") {
        echo $challenge;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $userID = $input['entry'][0]['messaging'][0]['sender']['id'];
    $message = $input['entry'][0]['messaging'][0]['message']['text'];

    $accessToken = "EAADZAfcarVMwBAJIZCmYNEkmGU7JKVr0xOUNRSHQhewLI0yVZBbDSsW9CXm46PdcHe8A0gwYDQJaJ9rGZCzTx7a0FMWsZBsTQxZBYK0bgapf2rnatX7KRt8VacbopZBZBRGnZC8IrTGeJZAmQtbMPBPHXzlsDq6zr5YVk3W17xMO4AXCRlxVNq7BVvhUwyZCjPhAsYZD";

    $url = "https://graph.facebook.com/v2.6/me/messages?access_token=$accessToken";

    if(!empty($input['entry'][0]['messaging'][0]['message'])) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://coronavirus-monitor.p.rapidapi.com/coronavirus/latest_stat_by_country.php?country=$message",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "x-rapidapi-host: coronavirus-monitor.p.rapidapi.com",
                "x-rapidapi-key: 676a9dace2msh26245f119ccfa57p17c33djsn05eea3176681"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response, true);
            if(!empty($data['latest_stat_by_country'][0]['country_name'])) {
                
                $countryname = $data['latest_stat_by_country'][0]['country_name'];
                $cases = $data['latest_stat_by_country'][0]['total_cases'];
                $newcases = $data['latest_stat_by_country'][0]['new_cases'];
                $deaths = $data['latest_stat_by_country'][0]['total_deaths'];
                $newdeaths = $data['latest_stat_by_country'][0]['new_deaths'];
                $recovered = $data['latest_stat_by_country'][0]['total_recovered'];
                $reply = "Stats for ".$countryname.", \\n\\nTotal Cases: ".$cases." \\nNew Cases: ".$newcases." \\nTotal Deaths: ".$deaths." \\nNew Deaths: ".$newdeaths." \\nTotal Recovered: ".$recovered."";
            }
            else {
                $reply = "Reply with a valid country name to get Covid Stats!";
            }
        }
    }

    $jsonData = "{
        'recipient': {
            'id': $userID
        },
        'message': {
            'text': '$reply'
        }
    }";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    if(!empty($input['entry'][0]['messaging'][0]['message'])) {
        curl_exec($ch);
    }