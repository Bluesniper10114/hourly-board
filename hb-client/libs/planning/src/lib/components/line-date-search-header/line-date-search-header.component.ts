import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { DrpDownListModel } from '@hourly-board-workspace/shared/fuse';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'hb-admin-line-date-search-header',
  templateUrl: './line-date-search-header.component.html',
  styleUrls: ['./line-date-search-header.component.scss']
})
export class LineDateSearchHeaderComponent implements OnInit {
  // getting the data from parent component
  @Input() lines: DrpDownListModel[];
  @Input() dates: string[];

  // emit an event to parent component to get datasets with lines , dates , shifts
  @Output()
  getDataSets: EventEmitter<{
    lines: number[];
    dates: string[];
    shifts: string[];
  }> = new EventEmitter();

  shifts: string[];

  selectedLines: number[];
  selectedDates: string[];
  selectedShifts: string[];
  constructor(private fb: FormBuilder) {
    this.shifts = ['A', 'B', 'C'];
  }

  ngOnInit() {
    this.selectedShifts = ['A', 'B', 'C'];
  }

  onFilterChange(): void {
    // debugger;
    // checking the validation of the inputs ( lines , dates , shifts)
    if (this.selectedLines && this.selectedDates && this.selectedShifts) {
      //  call the api with these values
      this.getDataSets.emit({
        lines: this.selectedLines,
        dates: this.selectedDates,
        shifts: this.selectedShifts
      });
    } else {
      //  show warning for the user to select the required values
    }
  }
}
