CREATE FUNCTION [report].[Split]
(
    @String varchar(max) ,
    @Delimiter NCHAR(1)
)
RETURNS TABLE
AS
RETURN
(
    WITH Split(stpos,endpos)
    AS(
        SELECT 0 AS stpos, CAST(CHARINDEX(@Delimiter,@String) as varchar(max)) AS endpos
        UNION ALL
        SELECT endpos+1, CAST(CHARINDEX(@Delimiter,@String,endpos+1)AS varchar(max))
            FROM Split
            WHERE endpos > 0
    )
    SELECT 
        'ShiftType' = SUBSTRING(@String,stpos,COALESCE(NULLIF(endpos,0),LEN(@String)+1)-stpos)
    FROM Split
)