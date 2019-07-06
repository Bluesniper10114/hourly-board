/*
	Author/Date	:	Cristian Dinu, 03.08.2018
	Description	:	global procedure, check any object ID
	LastChange	:	
*/

CREATE PROCEDURE [global].[CheckObjectID]
	@ObjectSchema		nvarchar(50) = 'dbo',
	@ObjectTable		nvarchar(50),
	@ObjectColumnID		nvarchar(50) = N'ID',
	@ObjectID			nvarchar(50),
	@CheckIsNull		bit = 1,
	@ProcedureLogID		bigint
as
	declare @errorNumber	int = 16,
			@errorMessage	nvarchar(max),
			@cmdSql			nvarchar(max),
			@isObsolete		bit,
			@isActive		bit

	begin try
		-- check if ObjectName is NULL
		if @ObjectTable is NULL goto ObjectTableIsNull

		-- check if ObjectID is NULL
		if @CheckIsNull = 1 and @ObjectID is NULL goto ObjectIDisNull

		-- check if @ObjectID is valid
		set @cmdSql = N'select @ObjectID = [' + @ObjectColumnID + N'] from [' + @ObjectSchema + N'].[' + @ObjectTable + N'] where [' + @ObjectColumnID + N'] = ''' + @ObjectID + N''''
		exec sp_executesql @cmdSql, N'@ObjectID nvarchar(50) OUTPUT', @ObjectID OUTPUT
		if @@ROWCOUNT = 0 goto ObjectIDisMissing
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  N'SQL string command ''' + @cmdSql + N''' was executed with errors. Check if @ObjectTable value ([' + @ObjectSchema + '].[' + @ObjectTable + N']) has a corresponding database table. Original error message: ' + ERROR_MESSAGE()
		goto ErrorExit
	end catch
	
return(0)

ObjectTableIsNull:
	set @errorMessage = N'[DevError]Search ID in table [' + @ObjectSchema + N'].[' + @ObjectTable + N'] failed: @ObjectTable is NULL.'
	goto ErrorExit
ObjectIDisNull:
	set @errorMessage = N'[DevError]Search ID in table [' + @ObjectSchema + N'].[' + @ObjectTable + N'] failed: @ObjectID is NULL.'
	goto ErrorExit
ObjectIDisMissing:
	set @errorMessage = N'[DevError] ' + @ObjectColumnID + N'=' + @ObjectID + N' is missing in [' + @ObjectSchema + N'].[' + @ObjectTable + N'] table.'
	goto ErrorExit
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @ProcedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)

