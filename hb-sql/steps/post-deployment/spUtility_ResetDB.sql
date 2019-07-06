/****** Object:  StoredProcedure [utility].[spUtility_ResetDB]    Script Date: 29.03.2016 11:50:10 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[utility].[spUtility_ResetDB]') AND type in (N'P', N'PC'))
DROP PROCEDURE [utility].[spUtility_ResetDB]
GO

/****** Object:  StoredProcedure [utility].[spUtility_ResetDB]    Script Date: 29.03.2016 11:50:10 ******/
SET ANSI_NULLS OFF
GO
SET QUOTED_IDENTIFIER OFF
GO

/*
	Author/Date	:	Cristian Dinu, 14.12.2015
	Description	:	Delete Item table and all logs
	LastChange	:	02.08.2016, add SubProject and Workbench to the reset tables list
*/

CREATE procedure [utility].[spUtility_ResetDB]
as

DECLARE @targetTables TABLE
	(
		[SchemaName] varchar(450),
		[TableName] varchar(450),
		PRIMARY KEY([SchemaName], [TableName])
	)

 /* TRUNCATE ALL TABLES IN A DATABASE */
DECLARE @dropAndCreateConstraintsTable TABLE
        (
         DropStmt VARCHAR(MAX)
        ,CreateStmt VARCHAR(MAX)
        )
/* Gather information to drop and then recreate the current foreign key constraints  */
INSERT  @dropAndCreateConstraintsTable
        SELECT  DropStmt = 'ALTER TABLE [' + ForeignKeys.ForeignTableSchema
                + '].[' + ForeignKeys.ForeignTableName + '] DROP CONSTRAINT ['
                + ForeignKeys.ForeignKeyName + ']; '
               ,CreateStmt = 'ALTER TABLE [' + ForeignKeys.ForeignTableSchema
                + '].[' + ForeignKeys.ForeignTableName
                + '] WITH CHECK ADD CONSTRAINT [' + ForeignKeys.ForeignKeyName
                + '] FOREIGN KEY([' + ForeignKeys.ForeignTableColumn
                + ']) REFERENCES [' + SCHEMA_NAME(sys.objects.schema_id)
                + '].[' + sys.objects.[name] + ']([' + sys.columns.[name]
                + ']); '
        FROM    sys.objects
        INNER JOIN sys.columns
                ON ( sys.columns.[object_id] = sys.objects.[object_id] )
        INNER JOIN ( SELECT sys.foreign_keys.[name] AS ForeignKeyName
                           ,SCHEMA_NAME(sys.objects.schema_id) AS ForeignTableSchema
                           ,sys.objects.[name] AS ForeignTableName
                           ,sys.columns.[name] AS ForeignTableColumn
                           ,sys.foreign_keys.referenced_object_id AS referenced_object_id
                           ,sys.foreign_key_columns.referenced_column_id AS referenced_column_id
                     FROM   sys.foreign_keys
                     INNER JOIN sys.foreign_key_columns
                            ON ( sys.foreign_key_columns.constraint_object_id = sys.foreign_keys.[object_id] )
                     INNER JOIN sys.objects
                            ON ( sys.objects.[object_id] = sys.foreign_keys.parent_object_id )
                     INNER JOIN sys.columns
                            ON ( sys.columns.[object_id] = sys.objects.[object_id] )
                               AND ( sys.columns.column_id = sys.foreign_key_columns.parent_column_id )
                   ) ForeignKeys
                ON ( ForeignKeys.referenced_object_id = sys.objects.[object_id] )
                   AND ( ForeignKeys.referenced_column_id = sys.columns.column_id )
        WHERE   ( sys.objects.[type] = 'U' )
                AND ( sys.objects.[name] NOT IN ( 'sysdiagrams' ) )

/* SELECT * FROM @dropAndCreateConstraintsTable AS DACCT  --Test statement*/
DECLARE @DropStatement NVARCHAR(MAX)
DECLARE @RecreateStatement NVARCHAR(MAX)
/* Drop Constraints */
DECLARE Cur1 CURSOR READ_ONLY
FOR
        SELECT  DropStmt
        FROM    @dropAndCreateConstraintsTable
OPEN Cur1
FETCH NEXT FROM Cur1 INTO @DropStatement
WHILE @@FETCH_STATUS = 0
      BEGIN
            PRINT 'Executing ' + @DropStatement
            EXECUTE sp_executesql @DropStatement
            FETCH NEXT FROM Cur1 INTO @DropStatement
      END
CLOSE Cur1
DEALLOCATE Cur1

INSERT INTO @targetTables (SchemaName, TableName) VALUES
('dbo','ActualsLog'),
('dbo','BillboardLog'),
('dbo','CommentsDictionary'),
('dbo','Downtime'),
('dbo','DowntimeDictionary'),
('dbo','DowntimeDetails'),
('dbo','Error'),
('dbo','EscalatedDictionary'),
('dbo','ShiftLog'),
('dbo','ShiftLogBreak'),
('dbo','ShiftLogSignOff'),
('global','Setting'),
('import','ActualsLog'),
('import','ActualsLogChanges'),
('import','ActualsLogDeletedErrors'),
('import','ActualsLogErrors'),
('layout','Cell'),
('layout','Line'),
('layout','LineTag'),
('layout','Location'),
('layout','Monitor'),
('layout','PartNumber'),
('layout','Workbench'),
('layout','WorkbenchStatus'),
('layout','WorkbenchType'),
('log','Procedure'),
('log','ProcedureLog'),
('target','Daily'),
('target','Hourly'),
('target','PartNumber'),
('target','Type'),
('users','Account'),
('users','AccountLoginHistory'),
('users','AccountProvider'),
('users','AccountToken'),
('users','Feature'),
('users','Level'),
('users','Operator'),
('users','Profile'),
('ver','Tables');


	/* Change your schema appropriately if you don't want to use dbo */

DECLARE @DeleteTableStatement NVARCHAR(MAX)
DECLARE Cur2 CURSOR READ_ONLY
FOR
        SELECT  'TRUNCATE TABLE [' + SchemaName  + '].[' + TableName + ']'
        FROM    @targetTables t
/* Change your schema appropriately if you don't want to use dbo */
OPEN Cur2
FETCH NEXT FROM Cur2 INTO @DeleteTableStatement
WHILE @@FETCH_STATUS = 0
      BEGIN
		BEGIN TRY
			PRINT 'Executing ' + @DeleteTableStatement
			EXECUTE sp_executesql @DeleteTableStatement
			FETCH NEXT FROM Cur2 INTO @DeleteTableStatement
		END TRY
		BEGIN CATCH
			SELECT ERROR_NUMBER(), ERROR_MESSAGE()
			PRINT 'Error: ' + CONVERT(nvarchar(10), ERROR_NUMBER()) + ' ' + ERROR_MESSAGE()
		END CATCH
      END
CLOSE Cur2
DEALLOCATE Cur2
/* Recreate foreign key constraints  */
DECLARE Cur3 CURSOR READ_ONLY
FOR
        SELECT  CreateStmt
        FROM    @dropAndCreateConstraintsTable
OPEN Cur3
FETCH NEXT FROM Cur3 INTO @RecreateStatement
WHILE @@FETCH_STATUS = 0
      BEGIN
            PRINT 'Executing ' + @RecreateStatement
            EXECUTE sp_executesql @RecreateStatement
            FETCH NEXT FROM Cur3 INTO @RecreateStatement
      END
CLOSE Cur3
DEALLOCATE Cur3



RETURN (0);

ErrorExit:
	return(-1);
GO
