<?php
use PHPUnit\Framework\TestCase;
use Api\Ads\AdsController;
use Api\Ads\AdsModel;
use DAL\AdsDAL;

class AdsTest extends TestCase
{
	public static function setUpBeforeClass()
	{
		\Settings\Config::Init("api");
	}

	/**
	 *
	 */
	public function ViewRandomAds()
	{
		$model = new AdsModel();
		$profileId = 1;
		$limit = 100;

		$exptected = [
			['id' => 1,
			'SalesCompanyId' => 1,
			'Title' => 'Title',
			'Subtitle' => 'SubTitle',
			'Picture' => 'http://link',
			'AdLink' => 'http://link',
			'Description' => 'Description',
			'StartDate' => '2011/1/1',
			'EndDate' => '2011/1/1',
			'ProductStatusId' => 1,
			'CostPerClick' => 1,
			'CostPerMille' => 1,
			'ProductAdTypeId' => 1,
			'Price' => 100],
			['id' => 2,
			'SalesCompanyId' => 1,
			'Title' => 'Title',
			'Subtitle' => 'SubTitle',
			'Picture' => 'http://link',
			'AdLink' => 'http://link',
			'Description' => 'Description',
			'StartDate' => '2011/1/1',
			'EndDate' => '2011/1/1',
			'ProductStatusId' => 1,
			'CostPerClick' => 1,
			'CostPerMille' => 1,
			'ProductAdTypeId' => 1,
			'Price' => 100],
		];

		$mockDal = $this->createMock(AdsDAL::class);
		$mockDal->method('ViewRandomAds')
			->willReturn($exptected);

		$resultArray = $model->ViewRandomAds($profileId, $limit);
		$this->assertEquals($resultArray, $exptected, "Expecting no results");
	}

	/**
	 * 
	 */
	public function ViewAd()
	{
		$model = new AdsModel();
		$profileId = 1;
		$limit = 100;

		$exptected = [
			'id' => 1,
			'SalesCompanyId' => 1,
			'Title' => 'Title',
			'Subtitle' => 'SubTitle',
			'Picture' => 'http://link',
			'AdLink' => 'http://link',
			'Description' => 'Description',
			'StartDate' => '2011/1/1',
			'EndDate' => '2011/1/1',
			'ProductStatusId' => 1,
			'CostPerClick' => 1,
			'CostPerMille' => 1,
			'ProductAdTypeId' => 1,
			'Price' => 100];

		$mockDal = $this->createMock(AdsDAL::class);
		$mockDal->method('ViewAd')
			->willReturn($exptected);

		$resultArray = $model->ViewAd($profileId, $limit);
		$this->assertEquals($resultArray, $exptected);
	}

}