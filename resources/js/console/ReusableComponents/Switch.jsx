import React from 'react'
import PropTypes from 'prop-types';
import ReactSwitch from 'react-switch';

export default function Switch(props) {

    return <ReactSwitch 
        checkedIcon={false}
        uncheckedIcon={false}
        onColor="#896c6b"
        offColor="#777" // from colors.scss
        width={35}
        height={20}
        handleDiameter={21}
        boxShadow={"0 0 0px 1px " + (props.checked ? "#896c6b" : "#777")}
        activeBoxShadow="0 0 5px 1px #896c6b"
        {...props} />;

}

Switch.propTypes = {
    checked: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired
}