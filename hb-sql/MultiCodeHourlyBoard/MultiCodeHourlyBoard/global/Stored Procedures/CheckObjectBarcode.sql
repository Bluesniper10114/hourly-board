/*
	Author/Date	:	Cristian Dinu, 03.08.2018
	Description	:	global procedure, check any object barcode and return ID
	LastChange	:	
*/

CREATE PROCEDURE [global].[CheckObjectBarcode]
	@ObjectSchema			nvarchar(50) = 'dbo',
	@ObjectTable			nvarchar(50),
	@ObjectColumnBarcode	nvarchar(50) = N'Barcode',
	@ObjectBarcode			nvarchar(50),
	@CheckIsNull			bit = 1,
	@ProcedureLogID			bigint,
	@ObjectID				bigint OUTPUT
as
	declare @errorNumber	int = 16,
			@errorMessage	nvarchar(max),
			@cmdSql			nvarchar(max),
			@isObsolete		bit,
			@isActive		bit

	begin try
		-- check if ObjectName is NULL
		if @ObjectTable is NULL goto ObjectTableIsNull

		-- check if ObjectNarcode is NULL
		if @CheckIsNull = 1 and @ObjectBarcode is NULL goto ObjectBarcodeIsNull

		-- check if @ObjectID is valid
		set @cmdSql = N'select @ObjectID = ID from [' + @ObjectSchema + '].[' + @ObjectTable + N'] where ' + @ObjectColumnBarcode + N' = ''' + @ObjectBarcode + ''''
		exec sp_executesql @cmdSql, N'@ObjectBarcode nvarchar(50), @ObjectID bigint OUTPUT', @ObjectBarcode, @ObjectID OUTPUT
		if @@ROWCOUNT = 0 goto ObjectIDisMissing
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  N'SQL string command ''' + @cmdSql + N''' was executed with errors. Check if @ObjectTable value ([' + @ObjectSchema + '].[' + @ObjectTable + N']) has a corresponding database table. Original error message: ' + ERROR_MESSAGE()
		goto ErrorExit
	end catch
	
return(0)

ObjectTableIsNull:
	set @errorMessage = N'[DevError]@ObjectTable is NULL.'
	goto ErrorExit
ObjectBarcodeIsNull:
	set @errorMessage = N'[DevError]@ObjectBarcode is NULL.'
	goto ErrorExit
ObjectIDisMissing:
	set @errorMessage = N'[DevError] ''' + @ObjectColumnBarcode + N' = ''' + @ObjectBarcode + N''' is missing in [' + @ObjectSchema + N'].[' + @ObjectTable + N'] table.'
	goto ErrorExit
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @ProcedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)

