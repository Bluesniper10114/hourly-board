CREATE TABLE [layout].[Monitor]
(
	[ID]		[int] IDENTITY(1,1) NOT NULL,
	[Location]	[nvarchar](50) NOT NULL,
	[Description] [nvarchar](255) NOT NULL,
	[IPAddress] [nvarchar](50) NULL CONSTRAINT [DF_Monitor_IPAddress]  DEFAULT (N'255.255.255.255'),
	[LocationID] CHAR(2) NOT NULL,
	[LineID]	[smallint] NULL,
	[Deleted] [bit] NOT NULL CONSTRAINT [DF_Monitor_Deleted]  DEFAULT ((0)),
	CONSTRAINT [PK_Monitor] PRIMARY KEY CLUSTERED ([ID] ASC),
	CONSTRAINT [CK_Monitor_IP] CHECK  (([IPAddress] like '%_.%_.%_.%_' AND NOT [IPAddress] like '%.%.%.%.%' AND NOT [IPAddress] like '%[^0-9.]%' AND NOT [IPAddress] like '%[0-9][0-9][0-9][0-9]%' AND NOT [IPAddress] like '%[3-9][0-9][0-9]%' AND NOT [IPAddress] like '%2[6-9][0-9]%' AND NOT [IPAddress] like '%25[6-9]%')),
	CONSTRAINT [FK_Monitor_Line] FOREIGN KEY([LineID]) REFERENCES [layout].[Line] ([ID]),
	CONSTRAINT [FK_Monitor_Location] FOREIGN KEY([LocationID]) REFERENCES [layout].[Location] ([ID])
)

GO

CREATE TRIGGER [layout].[Monitor_CheckUniqueConstraints_InsertUpdate]
    ON [layout].[Monitor]
    FOR INSERT, UPDATE
AS
BEGIN
    SET NoCount ON

	declare @locationID char(2),
			@ipAddress nvarchar(50)

	if UPDATE(LocationID) or UPDATE(IPAddress)
	begin
		select top 1
			@locationID = LocationID,
			@ipAddress = IPAddress
		from layout.Monitor
		where Deleted = 0
		group by LocationID, IPAddress
		having COUNT(*) > 1

		if @locationID is not NULL
		begin
			rollback tran
			raiserror(N'With present monitor modifications you will have 2 or more records with the same combination Location / IP address <%s / %s>.', 16, 1, @locationID, @ipAddress)
		end
	end

END