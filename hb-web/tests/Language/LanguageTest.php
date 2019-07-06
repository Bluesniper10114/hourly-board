<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests all languages agains the English language files
 */
class LanguageTest extends TestCase
{
	/**
	 * @test
	 * @dataProvider LanguageProvider
	 */
	public function Language($englishCode, $languageCode)
	{
		$languageFiles = $this->GetLanguageFiles();

		// load each language file and check against the English one
		foreach ($languageFiles as $languageFile)
		{
			$en = $this->GetLanguage($englishCode, $languageFile);
			$language = $this->GetLanguage($languageCode, $languageFile);
			$missingTags = array();

			// check if tags in the English file are identical to the tags in the language file
			$isValid = $this->AllLanguageTagsExist($language, $en, $missingTags);
			$this->assertEquals($missingTags, [], "There are missing tags");
		}
	}

	private function GetLanguage($languageCode, $fileName)
	{
		$languagePath = dirname(__DIR__)."../../language/$languageCode/$fileName";
		$language = include($languagePath);
		return $language;
	}

	private function AllLanguageTagsExist($language, $en, &$missingTags)
	{
		$missingTags = array();
		foreach ($en as $key => $value)
		{
			if(!isset($language[$key]))
			{
				$this->Log("Missing $key tag.<br/>");
				array_push($missingTags, $key);
			}
			else
			{
				if(is_array($en[$key])) // check for specific inner-members (deals, ads, etc...)
				{
					foreach ($en[$key] as $innerKey => $innerValue)
					{
						if(!isset($language[$key][$innerKey]))
						{
							$this->Log("Missing $key.$innerKey tag.<br/>");
							array_push($missingTags, "$key.$innerKey");
						}
						else
						{
							if(is_array($en[$key][$innerKey])) // check for labels member (titles, placeholders, help)
							{
								foreach ($en[$key][$innerKey] as $inceptionKey => $inceptionValue)
								{
									if(!isset($language[$key][$innerKey][$inceptionKey]))
									{
										$this->Log("Missing $key.$innerKey.$inceptionKey tag.<br/>");
										array_push($missingTags, "$key.$innerKey.$inceptionKey");
									}
								}
							}
						}
					}
				}
			}
		}
		return count($missingTags) == 0;
	}

	public function LanguageProvider()
	{
		return [
			["en", "de"],

			["en", "fr"],

			["en", "it"],

			["en", "nl"],

			["en", "en"],
		];
	}

	private function GetLanguageFiles()
	{
		return [
			"ad.php",
			"adreport.php",
			"ads.php",
			"club.php",
			"clubgroup.php",
			"clubrequest.php",
			"companies.php",
			"company.php",
			"coupon.php",
			"coupons.php",
			"couponvalidation.php",
			"couponvalidationlog.php",
			"coupon.php",
			"deal.php",
			"dealcategories.php",
			"deals.php",
			"email.php",
			"inquiries.php",
			"inquiry.php",
			"main.php",
			"menu.php",
			"moments.php",
			"reportedcomments.php",
			"setting.php",
			"settings.php",
			"user.php",
			"useradd.php",
			"useredit.php",
			"users.php",
		];
	}

	private function Log($message)
	{
		print($message);
	}

}
?>