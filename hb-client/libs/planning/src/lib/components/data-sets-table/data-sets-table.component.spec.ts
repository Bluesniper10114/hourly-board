import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DataSetsTableComponent } from './data-sets-table.component';

describe('DataSetsTableComponent', () => {
  let component: DataSetsTableComponent;
  let fixture: ComponentFixture<DataSetsTableComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DataSetsTableComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DataSetsTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
