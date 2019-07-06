import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { HoursModel, BillboardTotalsModel } from '../../models/billboard.model';
import { fuseAnimations } from '@hourly-board-workspace/shared/fuse';

@Component({
  selector: 'hb-billboard-hours-table',
  templateUrl: './billboard-hours-table.component.html',
  styleUrls: ['./billboard-hours-table.component.scss'],
  animations: fuseAnimations
})
export class BillboardHoursTableComponent implements OnInit {
  @Input() hoursData: HoursModel[];
  @Input() totals: BillboardTotalsModel;

  @Output() addComment: EventEmitter<HoursModel> = new EventEmitter();
  @Output() editComment: EventEmitter<HoursModel> = new EventEmitter();
  @Output() addEscalation: EventEmitter<HoursModel> = new EventEmitter();
  @Output() editEscalation: EventEmitter<HoursModel> = new EventEmitter();
  @Output() openDownTime: EventEmitter<HoursModel> = new EventEmitter();
  @Output()
  signOffHour: EventEmitter<{
    hourlyId: number;
    operatorBarcode: string;
  }> = new EventEmitter();

  constructor() {}

  onAddComment(hour: HoursModel): void {
    this.addComment.emit(hour);
  }

  onAddEscalation(hour: HoursModel): void {
    this.addEscalation.emit(hour);
  }

  onEditComments(hour: HoursModel): void {
    this.editComment.emit(hour);
  }

  onEditEscalations(hour: HoursModel): void {
    this.editEscalation.emit(hour);
  }

  onSignOffHour(event, hour: HoursModel): void {
    debugger;
    const payload: { hourlyId: number; operatorBarcode: string } = {
      hourlyId: hour.id,
      operatorBarcode: hour.signoff
    };
    this.signOffHour.emit(payload);
  }

  onOpenDownTime(hour: HoursModel): void {
    this.openDownTime.emit(hour);
  }

  ngOnInit() {}
}
