export class BillboardModel {
  header?: BillboardHeaderModel;
  hours?: HoursModel[];
  totals?: BillboardTotalsModel;

  constructor(billboard: BillboardModel) {
    this.header = new BillboardHeaderModel(
      billboard.header ? billboard.header : {}
    );
    this.hours = [];
    this.totals = {
      totalAchieved: 0,
      totalDefects: 0,
      totalTargets: 0,
      totalDowntime: 0
    };
    billboard.hours.forEach(hour => {
      hour = new HoursModel(hour);
      this.totals.totalAchieved += hour.achieved;
      this.totals.totalDefects += hour.defects;
      this.totals.totalTargets += hour.target;
      this.totals.totalDowntime += hour.downtime;
      this.hours.push(hour);
    });
  }
}

export class BillboardHeaderModel {
  date?: string;
  shift?: string;
  lineName?: string;
  locationName?: string;
  deliveryTime?: number;
  maxHourProduction?: number;
  timeStamp?: string;
  shiftLogSignOffId?: number;
  constructor(header: BillboardHeaderModel) {
    this.shiftLogSignOffId = header.shiftLogSignOffId;
    this.date = header.date;
    this.shift = header.shift;
    this.lineName = header.lineName;
    this.locationName = header.locationName;
    this.deliveryTime = +header.deliveryTime;
    this.maxHourProduction = +header.maxHourProduction;
    this.timeStamp = header.timeStamp;
  }
}

export class HoursModel {
  hourInterval?: string;
  target?: number;
  cumulativeTarget?: number;
  achieved?: number;
  cumulativeAchieved?: number;
  defects?: number;
  downtime?: number;
  signoff?: string;
  id?: number;
  closed?: boolean;
  firstOpen?: boolean;
  escalations?: string;
  comments?: string;
  escalationsArray?: string[];
  commentsArray?: string[];
  constructor(hour: HoursModel) {
    // debugger;
    this.id = +hour.id;
    this.hourInterval = hour.hourInterval;
    this.target = +hour.target;
    this.cumulativeTarget = +hour.cumulativeTarget;
    this.achieved = +hour.achieved;
    this.cumulativeAchieved = +hour.cumulativeAchieved;
    this.defects = +hour.defects;
    this.downtime = +hour.downtime;
    this.signoff = hour.signoff;
    this.firstOpen = hour.firstOpen;
    this.closed = hour.closed;
    // debugger;
    this.comments = hour.comments;
    this.escalations = hour.escalations;
    this.commentsArray = splitString(hour.comments);
    this.escalationsArray = splitString(hour.escalations);
  }
}

export class BillboardTotalsModel {
  totalAchieved?: number;
  totalDefects?: number;
  totalTargets?: number;
  totalDowntime?: number;
}

export const splitString = (str: string): string[] => {
  // debugger;
  let arr = [];
  if (!str) {
    return arr;
  } else {
    const endIndex = str.lastIndexOf('|');
    if (endIndex !== -1) {
      const trimmed = str.substring(0, endIndex);
      arr = trimmed.split('|');
    }
  }

  return arr;
};

export function findTextInRegx(strg: string, matcher: string): boolean {
  const Regx = new RegExp(strg);
  // debugger;
  if (Regx.test(matcher)) {
    return true;
  } else {
    return false;
  }
}
