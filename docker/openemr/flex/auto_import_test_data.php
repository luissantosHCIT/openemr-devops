#!/usr/bin/env php
<?php
/**
 * =======================================
 * OpenEMR Automated Test Data Import
 * =======================================
 * This script attempts to perform a bulk push of FHIR resources to the FHIR API in OpenEMR and import patient data.
 *
 * Usage:
 *   php auto_import_codes.php [dir_root]
 *
 * Example:
 *   php auto_import_codes.php
 *   php auto_import_codes.php codes
 * ============================================================================
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 MedicalMasses L.L.C. <https://medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\OEGlobalsBag;

$sitePath = '/var/www/localhost/htdocs/openemr';
$type = 'FHIR_TEST_DATA';

$_GET['site'] = 'default';
$ignoreAuth = true;
$sessionAllowWrite = true;
chdir($sitePath);

// Load OpenEMR's Composer autoloader
// This gives us access to all OpenEMR classes, including the Installer class
require_once 'interface/globals.php';
require_once 'library/standard_tables_capture.inc.php';

function import_bulk_fhir(string $type): void
{
    $dirScripts = OEGlobalsBag::getInstance()->getString('temporary_files_dir') . "/$type";

    foreach (glob("$dirScripts/*.json") as $resource) {
        echo $resource;
    }
}

function import_zip(string $file): void {
    global $type;

    # Copy to temp
    echo " [" . $type . "] Copying file => " . $file  . "!\n";
    if (!temp_copy($file, $type)) {
        error_log("Failed to copy " . $file . " of type " . $type);
        return;
    }

    # Unpack
    echo " [" . $type . "] Uncompressing file => " . $file  . "!\n";
    if (!temp_unarchive($file, $type)) {
        error_log("Failed to unzip " . $file . " of type " . $type);
    }

    # Import data
    echo " [" . $type . "] Importing file => " . $file  . "!\n";
    import_bulk_fhir($type);

    # Cleanup
    echo " [" . $type . "] Cleaning up import for file => " . $file  . "!\n";
    temp_dir_cleanup($type);
}



