/****** Object:  StoredProcedure [dbo].[MarkVersion]    Script Date: 4/21/2016 9:35:44 PM ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MarkVersion]') AND type in (N'P', N'PC'))
DROP PROCEDURE [dbo].[MarkVersion]
GO

/****** Object:  StoredProcedure [dbo].[MarkVersion]    Script Date: 4/21/2016 9:35:44 PM ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[ver].[MarkVersion]') AND type in (N'P', N'PC'))
DROP PROCEDURE [ver].[MarkVersion]
GO

/****** Object:  StoredProcedure [ver].[MarkVersion]    Script Date: 4/21/2016 9:35:44 PM ******/
SET ANSI_NULLS OFF
GO
SET QUOTED_IDENTIFIER OFF
GO

/*
	Author/Date	:	Marian Brostean, 04.03.2017
	Description	:	Start or finish version increase
	LastChange	:	
*/
CREATE PROCEDURE [ver].[MarkVersion]
	@buildNumber INT,
	@fullVersion NVARCHAR(MAX) = null,
	@description NVARCHAR(MAX) = null,
	@start BIT
AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @exists BIT
	SELECT @exists = count(1) from [ver].[Version] where [ID] = @buildNumber

	IF (@start = 1)
	BEGIN
		IF (@exists <> 0) 
		BEGIN
			RAISERROR('Version already exists!', 16, 1)
			RETURN (-1)
		END

		-- Insert statements for procedure here
		INSERT INTO [ver].[Version]
			([ID],
			[Version],
			[VersionDescription],
			[DateStarted])
		VALUES
			(@buildNumber,
			@fullVersion,
			@description,	
			dbo.fsGetDate());
	END
	ELSE
	BEGIN
		IF (@exists = 0) 
		BEGIN
			RAISERROR('Version does not exist!', 16, 1)
			RETURN (-1)
		END

		UPDATE [ver].[Version]
		SET DateEnded = getdate()
		WHERE [ID] = @buildNumber
	END
	RETURN (0);
END
