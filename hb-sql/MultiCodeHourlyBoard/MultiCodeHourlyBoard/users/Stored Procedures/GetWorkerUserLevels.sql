/*
*  Returns profile fields given a profileId
*  @param: @profileId
*/
CREATE PROCEDURE [users].[GetWorkerUserLevels]
	@userProfileId bigint
AS
	SET NOCOUNT ON

	if (@userProfileId is null) return -1;

	select		ID, Name
    from		[users].[Level]
    WHERE		ID in (
		1,		--WarehouseManager
		2,		--Driver
		3)		--Operator
	return 1;