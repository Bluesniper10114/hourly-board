import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { MonitorModel } from '../../models/monitor.model';
import { MatTableDataSource } from '@angular/material';
import { fuseAnimations } from '@hourly-board-workspace/shared/fuse';

@Component({
  selector: 'hb-monitors-list-table',
  templateUrl: './monitors-list-table.component.html',
  styleUrls: ['./monitors-list-table.component.scss'],
  animations: fuseAnimations
})
export class MonitorsListTableComponent implements OnInit {
  @Input() monitors: MonitorModel[];

  @Output() openMonitorById: EventEmitter<number> = new EventEmitter();

  displayedColumns = [
    'id',
    'location',
    'lineId',
    'ipAddress',
    'description',
    'locationName'
  ];
  dataSource = new MatTableDataSource();
  constructor() {}

  onOpenMonitorById(monitorID: number): void {
    this.openMonitorById.emit(monitorID);
  }

  ngOnInit() {
    // debugger;
    this.dataSource.data = this.monitors;
  }
}
