CREATE TABLE [layout].[LineTag]
(
	[LineID] SMALLINT NOT NULL, 
    [Tag] NVARCHAR(50) NOT NULL,
    CONSTRAINT [PK_LineTag] PRIMARY KEY CLUSTERED ([LineID] ASC, Tag ASC),
    CONSTRAINT [FK_LineTag_Line] FOREIGN KEY ([LineID]) REFERENCES [layout].[Line] ([ID]),
)


GO

CREATE TRIGGER [layout].[LineTag_GenerateTags_InsertUpdateDelete]
    ON [layout].[LineTag]
    FOR DELETE, INSERT, UPDATE
    AS
    BEGIN
        SET NoCount ON
		
		declare @lines	table(ID smallint NOT NULL PRIMARY KEY, Tags nvarchar(MAX))
		declare @tags	nvarchar(MAX),
				@id		smallint = 0

		insert into @lines(ID)
		select LineID from Inserted
		union
		select LineID from Deleted

		while 1=1
		begin
			select top 1
				@id = ID, @tags = N''
			from @lines
			where ID > @id
			order by ID
			if @@ROWCOUNT = 0 break

			select @tags += Tag + N';'
			from layout.LineTag
			where LineID = @id

			update @lines
			set Tags = @tags
			where ID = @id
		end

		update l
		set Tags = _l.Tags
		from @lines _l
			inner join layout.Line l on _l.ID = l.ID

    END