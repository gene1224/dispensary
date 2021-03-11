<?php
if (!class_exists('WP_Importer')) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if (file_exists($class_wp_importer)) {
        require $class_wp_importer;
    }

}

if (!class_exists('WP_Import')) {
    $wrx_importer_class = ABSPATH . 'wp-content/plugins/wordpress-importer/wordpress-importer.php';
    if (file_exists($wrx_importer_class)) {
        require $wrx_importer_class;
    }
}



class QRX_Site_Import  extends WP_Import
{
    public function import_site_data($file_name)
    {
        
        $xml_file = ABSPATH . 'wp-content/uploads/templates/' . $file_name;
        error_log("LOADING XML FILE:".$xml_file);
        
        if (file_exists($xml_file)) {
            error_log("XML FILE STATUS: FOUND");
            $this->fetch_attachments = true;
            error_log("XML IMPORT FUNCTION CALLING");
            $this->import($xml_file);
        } else {
            error_log("Site Copy Failed Template Does not Exist");
        }

    }
}
