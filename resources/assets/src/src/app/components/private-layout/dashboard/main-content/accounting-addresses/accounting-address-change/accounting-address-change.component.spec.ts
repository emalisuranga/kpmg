import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountingAddressChangeComponent } from './accounting-address-change.component';

describe('AccountingAddressChangeComponent', () => {
  let component: AccountingAddressChangeComponent;
  let fixture: ComponentFixture<AccountingAddressChangeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccountingAddressChangeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountingAddressChangeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
