import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { BillboardHeaderModel } from '../../models/billboard.model';

@Component({
  selector: 'hb-billboard-header',
  templateUrl: './billboard-header.component.html',
  styleUrls: ['./billboard-header.component.scss']
})
export class BillboardHeaderComponent implements OnInit {
  @Input() headerData: BillboardHeaderModel;


  @Output()
  signOffShift: EventEmitter<{
    shiftLogSignOffId: number;
    operatorBarcode: string;
  }> = new EventEmitter();

  operatorBarcode: string;
  constructor() {}

  onSignOffShift($event): void {
    debugger;
    const payload: { shiftLogSignOffId: number; operatorBarcode: string } = {
      operatorBarcode: this.operatorBarcode,
      shiftLogSignOffId:this.headerData.shiftLogSignOffId
    };
    this.signOffShift.emit(payload);
  }

  ngOnInit() {}
}
