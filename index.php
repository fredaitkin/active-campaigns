<?php

/**
* Contacts App
*
* This is a single file app that fetches a list of contacts and displays them in a table.
*
* @author Melissa Aitkin
*/

// TODO convert app to React or some lightweight framework to create a more maintainable and updateable application
// TODO Add responsive CSS styling

// Enable app to run as non SSL site
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Get contacts
if (empty($_REQUEST['api_key'])) {
    echo "You must supply the api key";
    exit;
}
$api_key = $_REQUEST['api_key'];
$url = $protocol . 'productonboardingcustomerjourneys.api-us1.com/api/3/contacts?status=-1&orders%5Bemail%5D=ASC&api_key=' . $api_key;

// Start creating app page
echo '<html><body>';

try {
    $response = make_curl_request($url);
    $table = create_table($response->contacts);
    // Display table of contacts
    echo $table;
} catch (Exception $e) {
    echo $e->getMessage();
}

// End app page
echo '</body></html>';

/**
 * Make HTTP request via CURL
 *
 * @param string $url HTTP request url
 * @return object
 */
function make_curl_request($url) {
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER  => 1, CURLOPT_URL => $url]);

    $resp = curl_exec($curl);

    if (!curl_errno($curl)):
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($http_code !== 200):
            throw new Exception("An error occured retrieving contact list");
        endif;
    else:
        throw new Exception("An error occured retrieving contact list: " .  curl_error($curl));
    endif;

    curl_close($curl);
    return json_decode($resp);
}

/**
 * Create html table
 *
 * @param array $contacts List of contact objects
 * @return string Table in html format
 */
function create_table($contacts) {
    $table = '';
    if (!empty($contacts)):
        $table = '<table class="contacts">';
        $table .= '<thead class="border_bottom">';
        $table .= '<th><input type="checkbox" name="all_contacts" value="all_contacts"></td></th><th>Name</th><th>Email</th><th>Account</th><th>Phone number</th>';
        $table .= '</thead>';
        $table .= '<tbody>';

        foreach ($contacts as $contact):
            $table .= '<tr class="border_bottom">';
            $table .= '<td><input type="checkbox" name="contact_id" value="' . $contact->id . '"></td>';
            $table .= '<td>';

            // Set contact image
            if (!empty($contact->firstName) || !empty($contact->lastName)):
                $table .= '<span class="logo">' . strtoupper(substr($contact->firstName, 0, 1) . substr($contact->lastName, 0, 1)) . '</span>';
                $table .= '<a href="#">' . $contact->firstName . ' ' . $contact->lastName . '</a>';
            else:
                $table .= '<div><img class="logo" src="https://d226aj4ao1t61q.cloudfront.net/gjcq9h7qt_gravatar_camp_default_circle.png" width=24 alt="contact_pic"/> --</div>';
            endif;

            $table .= '</td>';
            $table .= '<td><a href="#">' . $contact->email . '</a></td>';
            $table .= '<td>' . (!empty($contact->orgname) ? $contact->orgname : '--') . '</td>';

            if (!empty($contact->phone)):
                // The phone number is stored in many different formats, strip and reformat
                $phone = preg_replace("/[^0-9]/", "", $contact->phone);
                $table .= '<td>' . '(' . substr($phone, 0, 3) . ') ' . substr($phone, 2, 3) . '-' . substr($phone, 5, 4) . '</td>';
            else:
                $table .= '<td>--</td>';
            endif;

            $table .= '</tr>';
        endforeach;

        $table .= '</tbody>';
        $table .= '</table>';
    endif;
    return $table;
}

?>

<style type="text/css">
    body {
        font-family: Arial, Helvetica, sans-serif;
        padding: 100px;
    }
    table.contacts {
        width: 80%;
        font-size: 12px;
        border:1pt solid #cedfeb;
        border-bottom:0;
    }
    thead.border_bottom th {
        border-bottom:1pt solid #cedfeb;
        text-align: left;
    }
    tr.border_bottom td {
        border-bottom:1pt solid #cedfeb;
        height: 40px;
    }
    img.logo {
        vertical-align: middle;
    }
    span.logo {
        background: #e3e3e3;
        border-radius: 50%;
        -moz-border-radius: 50%;
        -webkit-border-radius: 50%;
        color: #6e6e6e;
        display: inline-block;
        font-weight: bold;
        line-height: 24px;
        margin-right: 5px;
        text-align: center;
        width: 24px;
    }

</style>

<script>

    (function() {

        document.addEventListener('click', function (event) {

            // Check or uncheck all contacts when all_contacts check box is checked
            if (event.target.name !== undefined && event.target.name === 'all_contacts') {
                if (event.target.checked) {
                    select_contacts(true);
                } else {
                    select_contacts(false);
                }
            }

        }, false);

        function select_contacts(checked) {
            var checkboxes = document.getElementsByName("contact_id");
            var index;
            for (index = 0; index < checkboxes.length; index++) {
                checkboxes[index].checked = checked;
            }
        }

    })();

</script>