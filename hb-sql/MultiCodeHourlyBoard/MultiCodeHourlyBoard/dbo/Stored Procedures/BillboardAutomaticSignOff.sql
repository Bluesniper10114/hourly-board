/*
	Author/Date	:	Cristian Dinu, 13.11.2017
	Description	:	Set automatic sign off for hour and shift billboard
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[BillboardAutomaticSignOff]
as
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	set nocount on

	declare @autoUserID			int = users.AutomaticOperator(),
			@delayHourSignOff	tinyint,
			@delayShiftSignOff	tinyint
	
	select @delayHourSignOff = CONVERT(tinyint, [Value])
	from [global].[Setting]
	where [Key] = N'AUTOMATIC_BILBOARD_HOUR_SIGNOFF'

	select @delayShiftSignOff = CONVERT(tinyint, [Value])
	from [global].[Setting]
	where [Key] = N'AUTOMATIC_BILBOARD_SHIFT_SIGNOFF'

	-- hour sign off
	update bl
	set SignedOffOperatorID = @autoUserID, UpdateDate = [global].[GetDate]()
	from dbo.BillboardLog bl
		inner join [target].vHourly h on bl.TargetHourlyID = h.ID
		inner join dbo.ShiftLog sl on h.ShiftLogID = sl.ID
	where bl.SignedOffOperatorID is NULL
		and DATEDIFF(MINUTE, h.HourEnd, [global].[GetDate]()) >  @delayHourSignOff

	-- shift sign off
	update slso
	set [SignedOffOperatorID] = @autoUserID,
		[Automatic] = 1,
		[UpdateDate] = [global].[GetDate]()
	from dbo.ShiftLogSignOff slso
		inner join dbo.vShiftLog sl on slso.ShiftLogID = sl.ID
	where [SignedOffOperatorID] is NULL
		and DATEDIFF(MINUTE, sl.DataEnd, [global].[GetDate]()) >  @delayShiftSignOff

return(0);
;