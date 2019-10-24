import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SelectNameChangeComponent } from './select-name-change.component';

describe('SelectNameChangeComponent', () => {
  let component: SelectNameChangeComponent;
  let fixture: ComponentFixture<SelectNameChangeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SelectNameChangeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelectNameChangeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
