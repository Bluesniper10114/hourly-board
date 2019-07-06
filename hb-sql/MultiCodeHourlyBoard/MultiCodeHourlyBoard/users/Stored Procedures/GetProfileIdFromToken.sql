-- Selects the user profile id (in "id") of the user being authenticated with @token
CREATE PROCEDURE [users].[GetProfileIdFromToken]
	@token nvarchar(MAX)
AS
	SET NOCOUNT ON

	declare @userProfileId bigint = -1;

	select		@userProfileId = ua.ProfileID
    from		[users].AccountToken uat 
    inner join	Account ua on uat.AccountID = ua.ID
    WHERE		Token = @token

	if (@userProfileId is null) return -1;
	return @userProfileId;