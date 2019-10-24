import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BalanceSheetdateComponent } from './balance-sheetdate.component';

describe('BalanceSheetdateComponent', () => {
  let component: BalanceSheetdateComponent;
  let fixture: ComponentFixture<BalanceSheetdateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BalanceSheetdateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BalanceSheetdateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
