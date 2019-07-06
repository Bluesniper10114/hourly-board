CREATE VIEW [users].[vLoggedInUsers]
	AS 
	select w.ID WorkbenchID, w.Name, w.TypeID, p.ID ProfileID, p.FirstName, p.LastName, p.Barcode, l.[Name] [Level], [global].[GetDate]() [now], at.LoginTime, at.LogoutTime, at.Expire, at.Token 
	from [layout].Workbench w
		inner join [layout].[WorkbenchStatus] ws on ws.WorkbenchID = w.ID
		inner join [users].[Profile] p on p.ID = ws.LoggedInProfileID
		inner join [users].[Level] l on l.ID = p.LevelID
		inner join [users].[Account] a on a.ProfileID = p.ID
		left join [users].[AccountToken] at on a.ID = at.AccountID
	where at.IsActive = 1