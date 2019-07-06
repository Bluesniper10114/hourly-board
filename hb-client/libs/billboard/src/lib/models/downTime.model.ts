export interface DownTimeModel {
  timeStamp?: string;
  hourlyId?: number;
  intervals?: DownTimeRowModel[];
  reasons?: DownTimeReason[];
  forDate?: string;
}

export interface DownTimeRowModel {
  id?: number;
  machine?: string;
  timeInterval?: string;
  totalDuration?: number;
  reasons?: DownTimeReason[];

}

export interface DownTimeSaveDTO {
  hourlyId?: number;
  timeStamp?: string;
  downtimeReasons?: {
    downtimeId: number;
    reasons: DownTimeReason[];
  }[];
}

export interface DownTimeReason {
  comment?: string;
  downtimeId?: number;
  id: number;
  timeStamp: string;
  reason: number;
  duration: number;
}
