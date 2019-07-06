<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests all help files against the English help files.
 * It checks if help files are empty.
 * It checks if English and translated versions are identical (meaning that the help file is not translated)
 */
class HelpTest extends TestCase
{
	/**
	 * @test
	 * @dataProvider LanguageProvider
	 */
	public function Language($englishCode, $languageCode)
	{
		$languageFiles = $this->GetHelpFileList();
		foreach ($languageFiles as $languageFile)
		{
			$en = $this->GetHelpFilePath($englishCode, $languageFile);
			$language = $this->GetHelpFilePath($languageCode, $languageFile);
			$enContent = file_get_contents($en);
			$languageContent = file_get_contents($language);
			$this->assertNotEquals($languageContent, "", "Language $languageCode is empty");
			if ($englishCode !== $languageCode)
			{
				// only do the check for "other" languages
				$this->assertNotEquals($enContent, $languageContent, "Language $languageCode is not translated");
			}
		}
	}

	private function GetHelpFilePath($languageCode, $fileName)
	{
		return dirname(__DIR__)."../../language/$languageCode/$fileName";
	}

	public function LanguageProvider()
	{
		return [
//			["en", "de"],

//			["en", "fr"],

//			["en", "it"],

//			["en", "nl"],

			["en", "en"],
		];
	}

	private function GetHelpFileList()
	{
		return [
			"adreport_help.php",
			"ads_help.php",
			"ad_help.php",
			"clubgroup_help.php",
			"clubrequests_help.php",
			"clubrequest_help.php",
			"companies_help.php",
			"company_help.php",
			"coupons_help.php",
			"couponvalidationlog_help.php",
			"couponvalidation_help.php",
			"coupon_help.php",
			"dealcategories_help.php",
			"deals_help.php",
			"deal_help.php",
			"email_help.php",
			"inquiries_help.php",
			"inquiry_help.php",
			"moments_help.php",
			"reportedcomments_help.php",
			"settings_help.php",
			"setting_help.php",
			"useradd_help.php",
			"useredit_help.php",
			"users_help.php",
			"user_help.php",

		];
	}
}
?>