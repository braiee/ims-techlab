<?php
session_start();
include 'db-connect.php';

$results_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$starting_limit_number = ($page - 1) * $results_per_page;
$table = isset($_GET['table']) ? $_GET['table'] : 'ticketing'; // Default to 'ticketing'

$sql = ""; // Initialize SQL query variable
$count_sql = ""; // Initialize SQL count query variable

// Determine SQL queries based on the table
switch ($table) {
    case 'Vendor Owned Devices':
        $sql = "SELECT item_name, vendor_name, contact_person, purpose, turnover_tsto, return_vendor FROM vendor_owned LIMIT ?, ?";
        $count_sql = "SELECT COUNT(item_name) AS total FROM vendor_owned";
        break;

    case 'Categories':
        $sql = "SELECT categories_id, categories_name, abv FROM categories LIMIT ?, ?";
        $count_sql = "SELECT COUNT(categories_id) AS total FROM categories";
        break;

        case 'Supplies':
            $sql = "SELECT os.supp_id, os.unique_legends_id, os.office_name, os.custodian, os.remarks, os.status, os.date_added, c.categories_name, l.legends_name 
                    FROM office_supplies os
                    LEFT JOIN categories c ON os.categories_id = c.categories_id
                    LEFT JOIN legends l ON os.legends_id = l.legends_id
                    WHERE os.status <> 'deleted'
                    LIMIT ?, ?";
            $count_sql = "SELECT COUNT(supp_id) AS total FROM office_supplies WHERE status <> 'deleted'";
            break;
        
    case 'Location':
        $sql = "SELECT legends_name, abv FROM legends LIMIT ?, ?";
        $count_sql = "SELECT COUNT(legends_name) AS total FROM legends";
        break;

    case 'Creative Tools':
        $sql = "SELECT ct.creative_id, ct.unique_creative_id, ct.qty, ct.creative_name, ct.emei, ct.sn, ct.custodian, ct.rnss_acc, ct.remarks, ct.descriptions, c.categories_name, l.legends_name 
        FROM creative_tools ct
        LEFT JOIN categories c ON ct.categories_id = c.categories_id
        LEFT JOIN legends l ON ct.legends_id = l.legends_id
        LIMIT ?, ?";
        $count_sql = "SELECT COUNT(creative_id) AS total FROM creative_tools";
        break;

    case 'Gadgets/Devices':
        $sql = "SELECT dm.gadget_id, dm.gadget_name, dm.unique_gadget_id, c.categories_name, dm.ref_rnss, dm.owner, dm.color, dm.emei, dm.sn, dm.custodian, dm.rnss_acc, dm.condition, dm.purpose, dm.remarks, l.legends_name, dm.status 
        FROM gadget_monitor dm
        LEFT JOIN categories c ON dm.categories_id = c.categories_id
        LEFT JOIN legends l ON dm.legends_id = l.legends_id
        LIMIT ?, ?";
        $count_sql = "SELECT COUNT(gadget_id) AS total FROM gadget_monitor";
        break;

    case 'Product': // Handle 'Product' table
        $sql = "SELECT tagging, item, IFNULL(c.categories_name, '') AS category, IFNULL(descriptions, '') AS descriptions, IFNULL(color, '') AS color, IFNULL(imei, '') AS imei, IFNULL(sn, '') AS sn, IFNULL(custodian, '') AS custodian, IFNULL(rnss_acc, '') AS rnss_acc, IFNULL(remarks, '') AS remarks, IFNULL(`condition`, '') AS `condition`, IFNULL(purpose, '') AS purpose, IFNULL(status, '') AS status, IFNULL(l.legends_name, '') AS legends_name 
        FROM (
            SELECT 'Vendor Owned' AS tagging, item_name AS item, categories_id AS category, NULL AS descriptions, NULL AS color, NULL AS imei, NULL AS sn, contact_person AS custodian, NULL AS rnss_acc, NULL AS remarks, NULL AS `condition`, NULL AS purpose, status, NULL AS legends_id
            FROM vendor_owned
            UNION ALL
            SELECT 'Office Supplies' AS tagging, office_name AS item, categories_id AS category, NULL AS descriptions, NULL AS color, custodian AS custodian, NULL AS remarks, NULL AS `condition`, NULL AS purpose, status, NULL AS legends_id
            FROM office_supplies
            UNION ALL
            SELECT 'Gadgets/Devices' AS tagging, gadget_name AS item, categories_id AS category, NULL AS descriptions, color AS color, emei AS imei, sn AS sn, custodian AS custodian, NULL AS rnss_acc, NULL AS remarks, `condition` AS `condition`, purpose AS purpose, status, legends_id
            FROM gadget_monitor
            UNION ALL
            SELECT 'Creative Tools' AS tagging, creative_name AS item, categories_id AS category, descriptions AS descriptions, NULL AS color, emei AS imei, sn AS sn, custodian AS custodian, rnss_acc AS rnss_acc, remarks AS remarks, NULL AS `condition`, NULL AS purpose, status, legends_id
            FROM creative_tools
        ) AS combined_data
        LEFT JOIN categories c ON combined_data.category = c.categories_id
        LEFT JOIN legends l ON combined_data.legends_id = l.legends_id
        LIMIT ?, ?";

        $count_sql = "SELECT COUNT(*) AS total FROM (
            SELECT item_name FROM vendor_owned
            UNION ALL
            SELECT office_name FROM office_supplies
            UNION ALL
            SELECT gadget_name FROM gadget_monitor
            UNION ALL
            SELECT creative_name FROM creative_tools
        ) AS combined_data";
        break;

        case 'Borrowed Items':
            $sql = "
            SELECT 
                bi.borrow_id,
                bi.status,
                bi.return_date AS duration,
                bi.borrow_date,
                COALESCE(ct.creative_name, gm.gadget_name, os.office_name, vo.item_name) AS item_name,
                CASE 
                    WHEN ct.creative_id IS NOT NULL THEN 'Creative Tools'
                    WHEN gm.gadget_id IS NOT NULL THEN 'Gadget Monitor'
                    WHEN os.office_id IS NOT NULL THEN 'Office Supplies'
                    WHEN vo.vendor_id IS NOT NULL THEN 'Vendor Owned'
                    ELSE 'Unknown'
                END AS category,
                u.username  -- This fetches the username associated with the user_id
            FROM 
                borrowed_items bi
            LEFT JOIN creative_tools ct ON bi.item_id = ct.creative_id
            LEFT JOIN gadget_monitor gm ON bi.item_id = gm.gadget_id
            LEFT JOIN office_supplies os ON bi.item_id = os.office_id
            LEFT JOIN vendor_owned vo ON bi.item_id = vo.vendor_id
            LEFT JOIN users u ON bi.user_id = u.user_id  -- Joining the users table to fetch the username
            LIMIT ?, ?
            ";
            $count_sql = "SELECT COUNT(bi.borrow_id) AS total FROM borrowed_items bi";
            break;
        
    default:
        // Default to the 'ticketing' table if no valid table is provided
        $sql = ""; // Set to empty string since we don't fetch tickets
        break;
}

// Prepare and execute the main SQL query if SQL is not empty
$response = [];
if (!empty($sql)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $starting_limit_number, $results_per_page);
    $stmt->execute();    
    $result = $stmt->get_result();

    // Fetch data from the result set
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Calculate total number of results and total pages for pagination
    $total_results = $conn->query($count_sql)->fetch_assoc()['total'];
    $total_pages = ceil($total_results / $results_per_page);

    

    // Prepare response based on the table
    switch ($table) {
        case 'Vendor Owned Devices':
            $response = ['vendor' => $data, 'total_pages' => $total_pages];
            break;
        case 'Categories':
            $response = ['categories' => $data, 'total_pages' => $total_pages];
            break;
        case 'Supplies':
            $response = ['supplies' => $data, 'total_pages' => $total_pages];
            break;
        case 'Creative Tools':
            $response = ['creative_tools' => $data, 'total_pages' => $total_pages];
            break;
        case 'Location':
            $response = ['locations' => $data, 'total_pages' => $total_pages];
            break;
        case 'Gadgets/Devices':
            $response = ['gadget_monitor' => $data, 'total_pages' => $total_pages];
            break;
        case 'Product':
            $response = ['products' => $data, 'total_pages' => $total_pages];
            break;
            case 'Borrowed Items':
                $response = ['borrowed_items' => $data, 'total_pages' => $total_pages];
                break;
    
        default:
            // Handle default case if needed
            break;
    }

    // Send JSON response back to the client
    echo json_encode($response);
}
?>


