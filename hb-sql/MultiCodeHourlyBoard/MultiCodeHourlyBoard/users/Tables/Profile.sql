CREATE TABLE [users].[Profile] (
    [ID]			INT					IDENTITY (1, 1) NOT NULL,
    [Deleted]		BIT					CONSTRAINT [DF_UserProfile_Deleted] DEFAULT ((0)) NULL,
    [FirstName]		NVARCHAR (255)		NOT NULL,
    [LastName]		NVARCHAR (255)		NOT NULL,
    [LevelID]		SMALLINT			CONSTRAINT [DF_UserProfile_Level] DEFAULT ((0)) NOT NULL,
    [Barcode]		NVARCHAR (50)		NULL,
    [IsActive]		BIT					CONSTRAINT [DF_UserProfile_Active] DEFAULT ((1)) NULL,
    [OperatorID]	INT					NULL,
    [CreatedAt]		DATETIME	CONSTRAINT [DF_UserProfile_CreatedAt] DEFAULT (global.GetDate()) NOT NULL,
    [UpdatedAt]		DATETIME	NULL,
    CONSTRAINT [PK_Profile_ID] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [IX_Profile_Barcode] UNIQUE NONCLUSTERED ([Barcode] ASC), 
    CONSTRAINT [CK_Profile_Operator] CHECK ([OperatorID] is NULL or ([OperatorID] is not NULL and [Deleted] = 0) )
);
GO

CREATE TRIGGER [users].[Profile_CheckUniqueOperator_InsertUpdate]
    ON [users].[Profile]
    FOR INSERT, UPDATE
    AS
    BEGIN
        SET NoCount ON

		declare @operatorBarcode nvarchar(50)
		select @operatorBarcode = o.Barcode
		from (
			select OperatorID
			from users.[Profile]
			where OperatorID is not NULL
				and Deleted = 0
			group by OperatorID
			having COUNT(ID) > 1
		) p
			inner join users.Operator o on p.OperatorID = o.ID
			
		group by o.Barcode
		having COUNT(o.ID) > 1

		if @operatorBarcode is not NULL
		begin
			rollback tran
			raiserror(N'With present profile modifications you will have 2 or more users with the same operator (%s).', 16, 1, @operatorBarcode)
		end

    END