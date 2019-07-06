import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BillboardHoursTableComponent } from './billboard-hours-table.component';

describe('BillboardHoursTableComponent', () => {
  let component: BillboardHoursTableComponent;
  let fixture: ComponentFixture<BillboardHoursTableComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BillboardHoursTableComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BillboardHoursTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
