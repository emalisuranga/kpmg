import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddressChangeResubmitComponent } from './address-change-resubmit.component';

describe('AddressChangeResubmitComponent', () => {
  let component: AddressChangeResubmitComponent;
  let fixture: ComponentFixture<AddressChangeResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddressChangeResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddressChangeResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
