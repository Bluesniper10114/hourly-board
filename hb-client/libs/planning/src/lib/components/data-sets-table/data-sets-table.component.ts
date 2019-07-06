import { Component, OnInit, Input } from '@angular/core';
import { DataSetsModel, DataSetsLineModel } from '../../models/datasets.model';
import { GroupByPipe } from 'angular-pipes';

@Component({
  selector: 'hb-admin-data-sets-table',
  templateUrl: './data-sets-table.component.html',
  styleUrls: ['./data-sets-table.component.scss'],
  providers: [GroupByPipe]
})
export class DataSetsTableComponent implements OnInit {
  private _datasets: DataSetsModel;
  dataSetsGrouped: any[];
  planningType: string;
  hours = [1, 2, 3, 4, 5, 6, 7, 8];
  @Input()
  set datasets(value: DataSetsModel) {
    this._datasets = value;
    if (this._datasets) {
      //   grouping rows by Line Name { key: lineName , value : DataSetsLineModel[] }
      const dataGroupedByLine = this.groupBy.transform(
        this.datasets.rows,
        'lineName'
      );
      // Looping through the lines & grouping data by Date
      dataGroupedByLine.forEach(ln => {
        debugger;
        ln.value = this.groupBy.transform(ln.value, 'date');
        ln.dates = ln.value;
        // group data by shift
        ln.dates.forEach(dt => {
          debugger;
          dt.value = this.groupBy.transform(dt.value, 'shift');
          const dataGroupedByShift = dt.value;
          dt.shifts = [];
          // looing through the shifts and construct the required object for display
          dataGroupedByShift.forEach(
            (shift: { key: string; value: DataSetsLineModel[] }) => {
              // shift = { key:'shift' , value : DataSetsLineModel[0] will be the DY planning Type and [1] will be the  }
              const shiftToDisplay: DataSetsLineModel = this.buildShiftObject(shift);
              dt.shifts.push(shiftToDisplay);
            }
          );
        });

      });
      debugger;
      this.dataSetsGrouped = dataGroupedByLine;
    }
  }
  get datasets(): DataSetsModel {
    return this._datasets;
  }
  constructor(private groupBy: GroupByPipe) {}
  ngOnInit() {}


  buildShiftObject(shift:any):DataSetsLineModel {
    return  {
      shift: shift.key,
      lineName: shift.value[0].lineName,
      activeOnBillboard: shift.value[0].activeOnBillboard,
      dailyTargetId: shift.value[0].dailyTargetId,
      planningType:shift.value[0].planningType,
      date: shift.value[0].date,
      // should push all the types founded in  lines list
      planningTypes: this.buildPlanningTypeArray(shift)
    };
  }

  buildPlanningTypeArray(shift:any):any {
    const arr=[];
    shift.value.forEach(element => {
      const plShift = {
          type: element.planningType,
          targetsPerHour: element.targetsPerHour,
          totals:element.totals
      }
      arr.push(plShift);
    });

    return arr;
  }

}
