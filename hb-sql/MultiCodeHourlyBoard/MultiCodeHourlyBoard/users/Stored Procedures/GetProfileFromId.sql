/*
*  Returns profile fields given a profileId
*  @param: @profileId
*/
CREATE PROCEDURE [users].[GetProfileFromId]
	@userProfileId bigint
AS
	SET NOCOUNT ON

	select		FirstName, LastName, LevelID, Barcode
    from		[users].[Profile]
    where		ID = @userProfileId

	if (@userProfileId is null) return -1;
return @userProfileId;