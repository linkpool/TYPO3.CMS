<?php
namespace TYPO3\CMS\Core\Tests\Acceptance\Backend\Extensionmanager;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Codeception\Util\Locator;
use TYPO3\TestingFramework\Core\Acceptance\Step\Backend\Admin;

/**
 * Tests for the "Install list view" of the extension manager
 */
class InstalledExtensionsCest
{
    /**
     * @param Admin $I
     */
    public function _before(Admin $I)
    {
        $I->useExistingSession();

        $I->click('Extensions', '#menu');
        $I->switchToContentFrame();
        $I->waitForElementVisible('#typo3-extension-list');
    }

    /**
     * @param Admin $I
     */
    public function checkSearchFiltersList(Admin $I)
    {
        $I->seeNumberOfElements('#typo3-extension-list tbody tr[role="row"]', [10, 100]);

        // Fill extension search field
        $I->fillField('Tx_Extensionmanager_extensionkey', 'backend');
        $I->waitForElementNotVisible(Locator::contains('#typo3-extension-list', 'core'));

        // see 2 rows. 1 for the header and one for the result
        $I->seeNumberOfElements('#typo3-extension-list tbody tr[role="row"]', 3);

        // Look for extension key
        $I->see('backend', '#typo3-extension-list tbody tr[role="row"] td');

        // unset the filter
        $I->waitForElementVisible('#Tx_Extensionmanager_extensionkey ~button.close', 10);
        $I->click('#Tx_Extensionmanager_extensionkey ~button.close');
        $I->wait(1);
        $I->seeNumberOfElements('#typo3-extension-list tbody tr[role="row"]', [10, 100]);
    }

    /**
     * @param Admin $I
     */
    public function checkIfUploadFormAppears(Admin $I)
    {
        $I->cantSeeElement('.module-body .uploadForm');
        $I->click('a[title="Upload Extension .t3x/.zip"]', '.module-docheader');
        $I->seeElement('.module-body .uploadForm');
    }

    /**
     * @param Admin $I
     */
    public function checkUninstallingAndInstallingAnExtension(Admin $I)
    {
        $I->wantTo('Check if uninstalling and installing an extension with backend module removes and adds the module from the module menu.');
        $I->amGoingTo('uninstall extension belog');
        $I->switchToMainFrame();
        $I->seeElement('#system_BelogLog');

        $I->switchToContentFrame();
        $I->waitForElementVisible('//*[@id="typo3-extension-list"]/tbody/tr[@id="belog"]');
        $I->click('a[data-original-title="Deactivate"]', '//*[@id="typo3-extension-list"]/tbody/tr[@id="belog"]');

        $I->switchToMainFrame();
        $I->cantSeeElement('#system_BelogLog');

        $I->amGoingTo('install extension belog');
        $I->switchToMainFrame();
        $I->seeElement('.modulemenu-item-link');
        $I->cantSeeElement('#system_BelogLog');

        $I->switchToContentFrame();
        $I->waitForElementVisible('//*[@id="typo3-extension-list"]/tbody/tr[@id="belog"]');
        $I->click('a[data-original-title="Activate"]', '//*[@id="typo3-extension-list"]/tbody/tr[@id="belog"]');

        $I->switchToMainFrame();
        $I->seeElement('#system_BelogLog');
    }
}
